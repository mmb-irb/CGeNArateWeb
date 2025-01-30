<?php
namespace App\Models;

class Files extends Model {

	protected $table = 'files';

	//*****************************
	// DIRECTORY CREATION
	//*****************************

	// private functions related to createDIR

	private function getDirPath($id) {

		// get the real path of a dir (field on dir document)

		$r = reset($this->db->getDocuments($this->table, ['_id' => $id], []));

		return $r->path.'/';

	}

	private function updateParentChildren($dirId, $parent, $flag) {

		$r = reset($this->db->getDocuments($this->table, ["_id" => $parent], []));
			
		// add $dirId to children array
		$children = (array)$r->children;

		if($flag == 1) {
			// creating file or dir, add to children
			array_push($children, $dirId);
		}else{
			// deleting file or dir, remove from children
			$key = array_search($dirId, $children);	
			if($key !== false) {
				unset($children[$key]);
			}

		}

		// update parent children
		$this->db->updateDocument($this->table, ['_id' => $parent], ['$set' => ['children' => $children]]);		

	}

	private function createFileDocument($owner, $path, $name, $size, $parent, $pdbid = null) {

		$fileId = uniqid("", true);

		$this->updateParentChildren($fileId, $parent, 1);
		
		// once we have created the folder (or folders if is a new user), we create the document on DB
		$data = array(
			"_id" => $fileId,
			"owner" => $owner,
			"size" => $size,
			"type" => "file",
			"path" => $path.$name,
			"name" => $name,
			"mtime" => new \MongoDB\BSON\UTCDateTime(),
			"parent" => $parent,
			"running" => true 
		);

		if(isset($pdbid)) $data["id"] = $pdbid;

		// create document
		$this->db->insertDocument($this->table, $data);

		$this->user->updateDiskQuota($owner, $size, 1);

		return $fileId;

	}

	private function createDirDocument($owner, $path, $name, $parent, $status) {

		$dirId = uniqid("", true);

		if(isset($parent)) $this->updateParentChildren($dirId, $parent, 1);

		// Create the document on DB
		$data = array(
			"_id" => $dirId,
			"owner" => $owner,
			"size" => 0,
			"type" => "dir",
			"status" => $status,
			"path" => $path.$name,
			"name" => $name,
			"mtime" => new \MongoDB\BSON\UTCDateTime(),
			"children" =>	array(),
			"parent" => $parent 
		);

		if($status == 2) $data["isProject"] = true;

		// create document
		$this->db->insertDocument($this->table, $data);

		return $dirId;

	}

	// END private functions related to createDIR

	public function createDir($name, $parent, $owner, $status = 1) {

		// $name -> new dir real name
		// parent -> id parent folder

		if(isset($parent)) {
			$path = $this->global['filesPath'].$this->getDirPath($parent).$name;
			$dirOk = mkdir($path, 0755);
		} else { 
			$path = $this->global['filesPath'].$name;
			$dirOk = mkdir($path, 0755);

			// if user root folder, then let's create uploads
			/*if($dirOk) {
				$dirOk = mkdir($path."/".$this->global['uploads'], 0755);
			}else{
				rmdir($path);
				$this->logger->info("error mkdir " .$name);
	
				return false;
			}

			// if _uploads folder, then let's create .tmp
			if($dirOk) {
				$dirOk = mkdir($path."/.tmp", 0755);
			}else{
				rmdir($path);
				rmdir($path."/.tmp");
				$this->logger->info("error mkdir uploads");

				return false;
			}*/

			// if user root folder, then let's create uploads
			if($dirOk) {
				$dirOk = mkdir($path."/.tmp", 0755);
			}else{
				rmdir($path);
				$this->logger->info("error mkdir " .$name);
	
				return false;
			}

		
		}

		if(!$dirOk) {

			if(!isset($parent)) {
				//rmdir($path."/".$this->global['uploads']);
				rmdir($path."/.tmp");
			}
			rmdir($path);

			$this->logger->info("error creating ".$name." or .tmp");

			return false;

		}

		if(!isset($parent)) {

			//$path = $name.'/';

			$dirId = $this->createDirDocument($owner, "", $name, null, 1);
			
			//$upId = $this->createDirDocument($owner, $path, $this->global['uploads'], $dirId, 1);
			//$tmpId = $this->createDirDocument($owner, $path, ".tmp", $dirId, 0);

			// in this case we return uploads ID because we should copy the README into this folder
			//return $upId;
			return $dirId;


		}else {

			$path = $this->getDirPath($parent);

			// AQUEST status = 2 NO HAURIA DE SER SEMPRE AIXÍ, MILLOR PASSAR-LI DES DE CREATE DIR???
			$dirId = $this->createDirDocument($owner, $path, $name, $parent, $status);	

			return $dirId;

		}

	}


	//*****************************
	// FILE UPLOADING
	//*****************************


	public function downloadFileFromApi($fileid, $uid, $type, $name, $pdb) {

		//$path = $this->global['filesPath'].$uid.'/'.$this->global['uploads'].'/';
		$path = $this->global['filesPath'].$uid.'/.tmp/';

		if(!$this->utils->downloadFromApi($path, $fileid, $type, $name, $pdb)){

			return false;

		}
		
		$parent = $this->getDirID($this->global['uploads'], $uid);
		
		$size = filesize($path.$name);

		//return $this->createFileDocument($uid, $uid.'/'.$this->global['uploads'].'/', $name, $size, $parent, $fileid);
		return $this->createFileDocument($uid, $uid.'/.tmp/', $name, $size, $parent, $fileid);
			
	}

	public function saveFromString($uid, $name, $string, $dir, $fileid = null) {

		//$path = $this->global['filesPath'].$uid.'/'.$this->global['uploads'].'/';
		$path = $this->global['filesPath'].$uid.'/.tmp/';

		$file = fopen($path.$name, "w");
		fwrite($file, $string);
		fclose($file);
		
		$parent = $this->getDirID($this->global['uploads'], $uid);
		
		$size = filesize($path.$name);

		//return $this->createFileDocument($uid, $uid.'/'.$this->global['uploads'].'/', $name, $size, $parent, $fileid);
		return $this->createFileDocument($uid, $uid.'/.tmp/', $name, $size, $parent, $fileid);
			
	}

	public function uploadFile($pdbfile, $uid, $name, $fileid = null) {

		$size = $pdbfile->getSize();

		//$path = $this->global['filesPath'].$uid.'/'.$this->global['uploads'].'/';
		$path = $this->global['filesPath'].$uid.'/.tmp/';

		$parent = $this->getDirID($this->global['uploads'], $uid);
		
		$pdbfile->moveTo($path.$name);

		//return $this->createFileDocument($uid, $uid.'/'.$this->global['uploads'].'/', $name, $size, $parent, $fileid);
		return $this->createFileDocument($uid, $uid.'/.tmp/', $name, $size, $parent, $fileid);
			
	}


	public function createMol($uid, $name, $nameSmile, $dir) {

		//$path = $this->global['filesPath'].$uid.'/'.$this->global['uploads'].'/';
		$path = $this->global['filesPath'].$uid.'/.tmp/';
		$workdir = $this->global['diskPath'].'Web/'.$uid.'/.tmp';

		chdir($workdir);

		$command = "babel -ismi $nameSmile -omol $name --gen2D";

    exec($command,$op);
		
		$parent = $this->getDirID($this->global['uploads'], $uid);
		
		$size = filesize($path.$name);

		//return $this->createFileDocument($uid, $uid.'/'.$this->global['uploads'].'/', $name, $size, $parent, $fileid);
		return $this->createFileDocument($uid, $uid.'/.tmp/', $name, $size, $parent);
			
	}


	//*****************************
	// FILE COPY
	//*****************************


	public function copyFileByName($path, $name, $parent, $owner) {

		$d = $this->getDirPath($parent);
		$size = filesize($path.$name);

		if(!copy($path.$name, $this->global['filesPath'].$d.$name)) {

			$this->logger->info("error copying file ".$path.$name." to ".$d.$name);
			return false;
				
		}

		// create collection
		return $this->createFileDocument($owner, $d, $name, $size, $parent);

	}

	public function copyFileByID($fileID, $parent, $owner) {

		$file = $this->getData($fileID);

		$parts = explode("/", $this->getPath($fileID));

		$sliced = array_slice($parts, 0, -1); 
		$imploded = implode("/", $sliced);

		$path = $imploded.'/';

		return $this->copyFileByName($this->global['filesPath'].$path, $file->name, $parent, $owner);

	}

	//*****************************
	// CHANGE FILE / DIR NAME
	//*****************************

	public function changeName($id, $name){

		$oldpath = $this->getPath($id);

		$parts = explode("/", $oldpath);

		$sliced = array_slice($parts, 0, -1); 
		$imploded = implode("/", $sliced);

		$newpath = $imploded.'/'.$name;

		if(!(rename($this->global['filesPath'].$oldpath, $this->global['filesPath'].$newpath))) {

			$this->logger->info("error changing project folder to ".$name);
			return false;

		}	

		$this->db->updateDocument($this->table, ['_id' => $id], ['$set' => ['path' => $newpath, 'name' => $name]]);

		return true;

	}


	//**********************************
	// DIRECTORY CREATION AND DELETION
	//**********************************

	// private functions related to deleteUserTree

	private function removeDirectory($path) {
  
		foreach(new \DirectoryIterator($path) as $item) {
			if (!$item->isDot()) {
				if(!$item->isFile()) $this->removeDirectory($path.'/'.$item->getFilename());
				else unlink($path.'/'.$item->getFilename());
			}
		}

		rmdir($path);

	}	

	// END private functions related to deleteUserTree


	public function deleteUserTree($owner) {

		$this->db->deleteDocument($this->table, ['owner' => $owner]);

		$this->removeDirectory($this->global['filesPath'].$owner);	

	}


	public function generateTree($path, $owner) {

		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST );

		foreach ( $iterator as $item ) {

			// getting parent name
			$parentName = end(explode('/', $item->getPathInfo()));

			// getting parent relative path
			$parent = str_replace($this->global['filesPath'], "", $item->getPathInfo());

			// getting rid of all the files & directories started bi '.'
			if ((substr($item->getFilename(), 0, 1) !== '.') && (substr($parentName, 0, 1) !== '.')){

				if ($item->isDir()) {

					$this->createDirDocument($owner, $parent.'/', $item->getFilename(), $this->getDirIDByPath($parent, $owner), 1);
					
				} else {

					$this->createFileDocument($owner, $parent.'/', $item->getFilename(), filesize($item->getPathname()), $this->getDirIDByPath($parent, $owner)); 

				}

			}

		}	

	}

	// private functions related to deleteFolder

	private function getAllFilesFromProject($id, $results = array()) {

		$results[] = $id;

		$children = $this->getDirChildren($id)->children;

		if(is_array($children)){

			foreach($children as $c) {

				$results = $this->getAllFilesFromProject($c, $results);

			}
		}

		if($this->getData($id)->status == 2) {
		
			$tmpinputs = $this->meta->getProjectInput($id);

			foreach($tmpinputs[0]['files'] as $f) {

				$results[] = $f;

			}

		}

		return $results;

	}

	// END private functions related to deleteFolder


	public function deleteFolder($id, $owner) {

		$files = $this->getAllFilesFromProject($id);

		$path = $this->getPath($id);

		if($this->getData($id)->status == 2) {

			foreach($files as $f) {

				//if($f->type == "file") unlink($this->global['filesPath'].$this->getData($f)->path);
				if($this->getData($f)->type == "file") unlink($this->global['filesPath'].$this->getData($f)->path);


			}

		}

		$this->removeDirectory($this->global['filesPath'].$path);

		$size = 0;
		foreach($files as $f) {

			$size += $this->getData($f)->size;	

			$this->updateParentChildren($f, $this->getData($f)->parent, 0);

			$this->db->deleteDocument($this->table, ['_id' => $f, 'owner' => $owner]);
				
		}

				$this->jobs->deleteJob($id);

		$this->meta->changeStatusUploadsFiles($id);

		$this->meta->deleteProjectMeta($id);

		$this->user->updateDiskQuota($owner, $size, -1);
		
	}



	//*****************************
	// FILES UTILITIES
	//*****************************

	public function getDirIDByPath($path, $owner) {

		// get the ID of a dir from its name and owner

		$r = reset($this->db->getDocuments($this->table, ['path' => $path, 'owner' => $owner], []));

		return $r->_id;

	}


	public function getDirID($name, $owner) {

		// get the ID of a dir from its name and owner

		$r = reset($this->db->getDocuments($this->table, ['name' => $name, 'owner' => $owner], []));

		return $r->_id;

	}

	public function getData($id) {

		// get the real name of a dir or file depending on its $id

		return reset($this->db->getDocuments($this->table, ['_id' => $id], []));

	}

	public function getPath($id) {

		// get the real path of a file

		$r = reset($this->db->getDocuments($this->table, ['_id' => $id], []));

		return $r->path;

	}

	public function getParent($id) {

		// get the parent id of a file

		$r = reset($this->db->getDocuments($this->table, ['_id' => $id], []));

		return $r->parent;

	}

	public function getNameFromPath($id) {

		// get the parent name of a file or dir
		
		$r = reset($this->db->getDocuments($this->table, ['_id' => $id], []));

		$p = explode("/", $r->path);

		return end($p);

	}

	
	public function getRootDir($owner){

		return reset($this->db->getDocuments($this->table, ['owner' => $owner, 'type' => 'dir', 'parent' => null], []));

	}

	public function getDirChildren($id){

		return reset($this->db->getDocuments($this->table, ['_id' => $id], [

			"projection" => [
				"_id" => 0, 
				"children" => 1			
			]
	
		]));

	}

	public function deleteFile($id) {

		$path = $this->getPath($id);

		if(!unlink($this->global['filesPath'].$path)) {
	
			return false;
	
		}

		$parent = $this->getParent($id);

		$this->updateParentChildren($id, $parent, 0);

		$f = $this->getData($id);

		$this->db->deleteDocument($this->table, ['_id' => $id]);

		$this->user->updateDiskQuota($f->owner, $f->size, -1);


		return true;

	}

	public function updateStatus($projectID, $status) {

		$this->db->updateDocument($this->table, ['_id' => $projectID], ['$set' => ['status' => $status]]);	

		return true;

	}

	public function updateRunningStatus($id, $status) {

		$this->db->updateDocument($this->table, ['_id' => $id], ['$set' => ['running' => $status]]);	

		return true;

	}

	// RAW Utilities
	
	public function createRawDir($name, $path) {

		$p = $this->global['filesPath'].$path.'/'.$name;

		if(!mkdir($p, 0755)) {

			return false;

		}
		
		return $path.'/'.$name;

	}

	public function deleteRaw($path) {

		return $this->removeDirectory($path);
	
	}

	public function copyRawFile($origin, $destination, $name) {

		if(!copy($this->global['filesPath'].$origin, $this->global['filesPath'].$destination.'/'.$name)) {

			$this->logger->info("error copying file ".$this->global['filesPath'].$origin." to ".$this->global['filesPath'].$destination.'/'.$name);
			return false;
				
		}

		return true;

	}

}

