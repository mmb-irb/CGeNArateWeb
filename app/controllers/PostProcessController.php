<?php
namespace App\Controllers;

use App\Models\User;

class PostProcessController extends Controller {

	private function getData($id) {

		return $this->projects->getProjectData($id);
	
	}

	// END OF JOB

	private function sendFinishEmail($projectData) {

		$user = $projectData->email;
		$analysis = $projectData->analysis;
		$projectID = $projectData->_id;
    $absoluteURL = $this->global['absoluteURL'];
    $fromName = $this->global['fromName'];
    //$hashId = password_hash($user->_id, PASSWORD_DEFAULT);
    //$name = $user->name.' '.$user->surname;
 
    $this->mailer->send('mails/mail-end-job.html', ['analysis' => $analysis, 'absoluteURL' => $absoluteURL, 'projectID' => $projectID] , function($message) use ($user, $fromName){
      $message->to($user);
      $message->subject('MCDNA has finished!');
      $message->fromName($fromName);
    });

	}

	public function endOfJobs($request, $response, $args) {

		$projectData = $this->getData($args['id']);

		$this->projects->updateEndDate($args['id']);

		if($projectData->email != "") $this->sendFinishEmail($projectData);	

	}

	// AUTOCLEAN

	private function sendWarningEmail($projectData) {

		$usr = $projectData->email;
		$projectID = $projectData->_id;
    $absoluteURL = $this->global['absoluteURL'];
    $fromName = $this->global['fromName'];

    $this->mailer->send('mails/mail-warning-user.html', ['absoluteURL' => $absoluteURL, 'projectID' => $projectID] , function($message) use ($usr, $fromName){
      $message->to($usr);
      $message->subject('Your MCDNA project is about to expire!');
      $message->fromName($fromName);
    });

	}

	public function sendWarnUser($request, $response, $args) {

		$projectData = $this->getData($args["id"]);

		if($projectData->email != "") $this->sendWarningEmail($projectData);

	}

	public function warnUser($id) {

		$projectData = $this->getData($id);

		if($projectData->email != "") $this->sendWarningEmail($projectData);

	}

	private function removeDirectory($path) {                                                                                                                                    
  	foreach(new \DirectoryIterator($path) as $item) {                                                                                                                          
    	if (!$item->isDot()) {                                                                                                                                                   
      	if(!$item->isFile()) $this->removeDirectory($path.'/'.$item->getFilename());                                                                                           
      	else unlink($path.'/'.$item->getFilename());                                                                                                                           
     	}                                                                                                                                                                        
  	}                                                                                                                                                                          
                                                                                                                                                                            
  	rmdir($path);                                                                                                                                                              
  }

	private function removeProject($id) {

		$projectData = $this->getData($id);

		if(file_exists($this->global['filesPath'].$projectData->folder)) {
			$this->removeDirectory($this->global['filesPath'].$projectData->folder);
		}

		$this->projects->deleteProject($projectData->_id);	

	}

	// passos:
	// 1) fer les dues llistes
	// 2) crear fitxer.sh:
	// 		2.1) llistat de /warning/mail/{id} que s'han d'enviar
	// 		2.2) llistat de rm -r FOLDER que s'han d'eliminar
	// 		2.3) guardar log amb nombre total de mails i eliminats i a col·lecció cronjob
	// 3) enviar fitxer.sh a cues
	// 4) guardar registre a col·lecció cronjob (dades i pid)
	//

	public function autoClean($request, $response, $args) {

		$all_projects = $this->projects->getListOfProjects([], ['projection' => ['_id' => 1, 'uploadDate' => 1, 'endDate' => 1]]);

		// get samples _id's
		$samples = [];
		foreach($this->global['sample'] as $gsample) {
			$samples[] = $gsample[0];
		}

		// put in list1 all the id's that:
		// 1) are not samples
		// 2) are older than 90 days (uploadDate)
		$list1 = [];
		// put in list2 all the id's that:
		// 1) are not samples
		// 2) are 85 days old
		$list2 = [];
		foreach($all_projects as $project) {
			if(!in_array($project->_id, $samples)) {
				$now =  new \DateTime();

				$ud = $project->uploadDate->toDateTime();

				if($now->diff($ud)->days >= $this->global['cron']['expiration']) {
					$list1[] = $project->_id;
					$this->removeProject($project->_id);
				}

				if($now->diff($ud)->days == $this->global['cron']['warn-mail']) {
					$list2[] = $project->_id;
					$this->warnUser($project->_id);
				}

			}
		}

		echo "Auto clean done!<br><br>\n\n";
		echo sizeof($list2)." warning mail sent.<br>\n";
		echo sizeof($list1)." registers removed.";

	}

}
