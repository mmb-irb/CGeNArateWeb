<?php
namespace App\Models;

class Jobs extends Model {

	protected $table = 'jobs';

	public function createNewJob($pid, $projectID, $status, $owner) { 
		
		$data = array(
			"_id" => $projectID,
			"pid" => $pid,
			"owner" => $owner,
			"status" => $status,
			"mtime" => new \MongoDate()
		);

		// create document
		$this->db->insertDocument($this->table, $data);

		return true;
	
	}

	public function getRunningJobs($owner) {
			
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

	}


}


