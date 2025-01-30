<?php
namespace App\Models;

class Projects extends Model {

	protected $table = 'projects';

	public function createNewProject($data) { 
		
		// create document
		$this->db->insertDocument($this->table, $data);

		return true;
	
	}

	public function updatePID($projectID, $pid) {

		$this->db->updateDocument($this->table, ['_id' => $projectID], ['$set' => ['pid' => $pid]]);	

		return true;

	}

	public function updateEndDate($projectID) {

		$this->db->updateDocument($this->table, ['_id' => $projectID], ['$set' => ['endDate' => new \MongoDB\BSON\UTCDateTime()]]);	

		return true;

	}

	public function getProjectData($projectID) {

		return reset($this->db->getDocuments($this->table, ['_id' => $projectID], []));
	
	}

	public function getListOfProjects($query, $options) {

		return $this->db->getDocuments($this->table, $query, $options);
	
	}

	public function deleteProject($projectID) {

		$this->db->deleteDocument($this->table, ['_id' => $projectID]);	

		return true;

	}

	// Disk access
	private function checkFolderName($name) {

		if($name == "") return false;

		if(!preg_match("/^MCD/", $name)) return false;

		if(preg_match("/\.|\/|\\\|\-/", $name)) return false;

		return true;	

	}

	public function deleteCompleteProject($id) {

		$projectData = $this->getProjectData($id);

		if(file_exists($this->global['diskPath'].$projectData->folder) && $this->checkFolderName($projectData->folder)) {
			exec("rm -rf ".$this->global['diskPath'].$projectData->folder);
		}

		$this->deleteProject($id);	

	}


	/*public function getRunningJobs($owner) {
			
		$r = $this->db->getDocuments($this->table, ['owner' => $owner, 'status' => 4], []);

		return $r;
				
	}

	public function getFinishedJobs($owner) {
			
		$r = $this->db->getDocuments($this->table, ['owner' => $owner, 'status' => 5], []);

		return $r;
				
	}


	public function getUserJobs($owner, $limit) {
			
		$r = $this->db->getDocuments($this->table, ['owner' => $owner], ['sort' => [ 'mtime' => -1], 'limit' => $limit]);

		return $r;
				
	}

	public function updateStatus($projectID, $status) {

		$this->db->updateDocument($this->table, ['_id' => $projectID], ['$set' => ['status' => $status]]);	

		return true;

	}

	public function deleteUserJobs($owner) {

		$this->db->deleteDocument($this->table, ['owner' => $owner]);

	}

	public function getJobPid($id) {

		$r = $this->db->getDocuments($this->table, ['_id' => $id,], []);

		var_dump($r);

		return $r[0]->pid;
			
	}

	public function deleteJob($id) {

		$pid = $this->getJobPid($id);


		// TODO:
		if($this->sge->status($pid) == 4) $this->sge->stop($pid); 

		$this->db->deleteDocument($this->table, ['_id' => $id]);

	}*/


}


