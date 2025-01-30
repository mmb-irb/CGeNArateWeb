<?php
namespace App\Models;
//use App\Models\DB as db;


class User extends Model {

	protected $table = 'users';

	public function getUsersList() {
			
		$r = $this->db->getDocuments($this->table, [], []);
		
		return $r;

	}

	public function getUsersQuery($query, $options = []) {
			
		$r = $this->db->getDocuments($this->table, $query, $options);
		
		return $r;

	}


	public function getUser($id) {

		$r = $this->db->getDocuments($this->table, ['_id' => $id], []);
		
		return $r;

	}

	public function getUserByID($id) {

		$r = $this->db->getDocuments($this->table, ['id' => $id], []);
		
		return $r;

	}

	public function updateLastLogin($id) {
				
		$this->db->updateDocument($this->table, ['_id' => $id], ['$set' => ["lastLogin" => new \MongoDB\BSON\UTCDateTime()]]);		

	}


	public function userExists($id) {

		$r = $this->db->getDocuments($this->table, ['_id' => $id], []);

		if(empty($r) === true) return false;
		elseif(sizeof($r) > 0) return true;

	}

	public function userExistsByID($id) {

		$r = $this->db->getDocuments($this->table, ['id' => $id], []);

		if(empty($r) === true) return false;
		elseif(sizeof($r) > 0) return true;

	}

	public function createUser($data) {
	
		$data["lastLogin"] = new \MongoDB\BSON\UTCDateTime();
		$data["status"] = 1;
		$data["registrationDate"] = new \MongoDB\BSON\UTCDateTime();
		$data["diskQuota"] = $this->global['diskQuota'];
		$data["diskUsed"] = 0;
		$data["id"] = uniqid($this->global['shortProjectName'].'USER');

		$this->db->insertDocument($this->table, $data);

		return $data["id"];
		
	}

	public function updateUser($id, $fields) {
				
		$this->db->updateDocument($this->table, ['_id' => $id], ['$set' => $fields]);		

	}

	public function deleteUser($id) {

		$this->db->deleteDocument($this->table, ['_id' => $id]);
	
	}

	public function deleteUserComplete($id) {

		if(!$this->userExistsByID($id))  {
			//var_dump(false);
			return false;
		}

		$this->db->deleteDocument($this->table, ['id' => $id]);

		$this->files->deleteUserTree($id);

		$this->meta->deleteUserMeta($id);

		$this->jobs->deleteUserJobs($id);

		// TODO: stop all jobs pending by user
		//
		// get all pids from user
		// foreach pid $this->sge->stop(pid)
		//
		
		return true;

	}

	public function getDiskUsed($id) {

		$r = reset($this->db->getDocuments($this->table, ['id' => $id], []));

		return $r->diskUsed;

	}

	public function getDiskQuota($id) {

		$r = reset($this->db->getDocuments($this->table, ['id' => $id], []));

		return $r->diskQuota;

	}

	
	public function getTotalDiskQuota() {

		$r = $this->db->sum($this->table, 'diskQuota');

		return $r;
	}


	public function getTotalUsers() {

		$r = $this->db->count($this->table);

		return $r;
	}

	public function getTotalDiskUsed() {

		$r = $this->db->sum($this->table, 'diskUsed');

		return $r;
	}


	public function updateDiskQuota($id, $amount, $op) {

		$used = $this->getDiskUsed($id);

		$total = $used + ($amount*$op);

		$this->db->updateDocument($this->table, ['id' => $id], ['$set' => ['diskUsed' => $total]]);		

	}

}
