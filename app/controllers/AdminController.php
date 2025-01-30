<?php
namespace App\Controllers;

class AdminController extends Controller {

	private function getPremiumRequests() {

		$users = $this->user->getUsersQuery(['type' => 100, 'status' => 1], [
			"projection" => [
				"id" => 1,
				"name" => 1,
				"surname" => 1,
				"institution" => 1,
				"registrationDate" => 1
			], 
			"sort" => [
				"registrationDate" => -1
			]]);

		return $users;

	}

	private function getTotalDiskQuota() {

		$tdq = $this->global['totalDisk'];
		$tdu = $this->user->getTotalDiskUsed();

		$totalDisk = $this->utils->getSize($tdq);
		$usedDisk = $this->utils->getSize($tdu);
		$percentDisk = ($tdu/$tdq)*100;

		$d = [
			'totalDisk' => $totalDisk,
			'usedDisk' => $usedDisk,
			'percentDisk' => $percentDisk,
		];

		return $d;
	
	}

	private function getAverageDiskQuota() {

		$tu = $this->user->getTotalUsers();
		$tdu = $this->user->getTotalDiskUsed();
		$tdq = $this->user->getTotalDiskQuota();

		$totalDisk = $this->utils->getSize($tdq / $tu);
		$usedDisk = $this->utils->getSize($tdu / $tu);
		$percentDisk = ($tdu / $tdq) * 100;

		$d = [
			'totalDisk' => $totalDisk,
			'usedDisk' => $usedDisk,
			'percentDisk' => $percentDisk,
		];

		return $d;

	}


	public function dashboard($request, $response, $args) {

		$usersRequest = $this->getPremiumRequests();

		$diskQuota = $this->getTotalDiskQuota();

		$diskAverage = $this->getAverageDiskQuota();

		$usersList = $this->user->getUsersList();

		$countriesList = $this->countries->getCountriesList();

		$vars = [
			'page' => [
				'title' => 'Dashboard - '.$this->global['longProjectName'],
				'description' => 'Admin dashboard for '.$this->global['longProjectName'],
				'basename' => 'dashboard'
			],
			'usersRequest' => $usersRequest,
			'diskQuota' => $diskQuota,
			'diskAverage' => $diskAverage,
			'users' => $usersList,
			'countries' => $countriesList,
			'roles' => $this->global['roles'],
			'rolesColor' => $this->global['rolesColor'],
		];		

		$this->view->render($response, 'dashboard.html', $vars);
	
	}

	private function getDomainURL() {

    $headers = apache_request_headers();
    if(isset($headers["X-Forwarded-Host"])) $domainURL = $headers["X-Forwarded-Host"];
    else $domainURL = $headers["Host"];

    return $domainURL;
  
	}


	public function changeTypeOfUser($request, $response, $args) {

		$id = $args['id'];
		$ntype = (int)$args['ntype'];
		$otype = (int)$args['otype'];

		$user = reset($this->user->getUserByID($id));

		if(isset($user)) {
			
			$this->user->updateUser($user->_id, ["type" => $ntype]);

			if(($otype == 100) && ($ntype == 1)){

				$domainURL = $this->getDomainURL();
				$fromName = $this->global['fromName'];
				$name = $user->name.' '.$user->surname;

				$this->mailer->send('mails/mail-premium-accepted.html', ['domainURL' => $domainURL, 'name' => $name] , function($message) use ($user, $fromName){
					$message->to($user->email);
					$message->subject('Bioactive Compounds Premium account');
					$message->fromName($fromName);
				});

			}

			echo '{"status": 1}';

		
		}else{

			echo '{"status": 0, "msg": "No user found!"}';

		}
	
	}

	
	public function changeStatusOfUser($request, $response, $args) {

		$id = $args['id'];
		$status = (int)$args['status'];

		$user = reset($this->user->getUserByID($id));

		if(isset($user)) {
			
			$this->user->updateUser($user->_id, ["status" => $status]);
		
			echo '{"status": 1}';
		
		}else{

			echo '{"status": 0, "msg": "No user found!"}';

		}
	
	}

	public function modifyUserData($request, $response, $args) {

		$input = $request->getParsedBody();

		$id = $input['id'];

		$user = reset($this->user->getUserByID($id));

		if(isset($user)) {
			
			$this->user->updateUser($user->_id, [
				"name" => $input['name'], 
				"surname" => $input['surname'],
				"institution" => $input['institution'],
				"diskQuota" => (int)$input['diskQuota'],
				"country" => $input['country'],
				"type" => (int)$input['type']
			]);
		
			echo '{"status": 1}';
		
		}else{

			echo '{"status": 0, "msg": "No user found!"}';

		}
	
	}	

	public function JSGlobals($request, $response, $args) {

		// countries list
	
		$countries = $this->countries->getCountriesList();

		echo 'var countriesSelect = \'<select style="width: 100%!important;" class="selector form-control input-sm input-xsmall input-inline" id="select-countries"><option value="">Country</option>';
		
		foreach($countries as $c)	{

			if(isset($c['country'])) {

				$country = str_replace("'","&#39;",$c['country']);
				echo '<option value="'.$c['_id'].'">'.$country.'</option>';

			}

		}

		echo '</select>\';'."\n\n";

		// roles list

		$roles = $this->global['roles'];

		echo 'var rolesList = \'<ul class="dropdown-menu" role="menu">';
	
		foreach($roles as $k => $r)	echo '<li><a class="role-usr role'.$k.'" href="javascript:;">'.$r.'</a></li>';

		echo '</ul>\';'."\n\n";

		// roles select

		echo 'var rolesSelect = \'<select style="width: 100%!important;" class="selector form-control input-sm input-xsmall input-inline" id="select-type-user"><option value="">Role</option>';
	
		foreach($roles as $k => $r)	echo '<option value="'.$k.'">'.$r.'</option>';

		echo '</select>\';'."\n\n";

		// roles color

		$rolesColor = $this->global['rolesColor'];

		echo 'var rolesColor = {';

		foreach($rolesColor as $k => $r) {

			if(isset($r)) echo $k.':"'.$r.'",';
			else echo $k.':null,';


		}

		echo '};'."\n\n";

		echo 'var diskLimit = '.$this->global['totalDisk'].';'."\n\n";

		echo 'var wsTimeOut = '.$this->global['wsTimeOut'].';'."\n\n";

		return $response->withHeader('Content-type', 'application/javascript');
	
	}
	
}
