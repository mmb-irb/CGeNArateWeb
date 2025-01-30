<?php 

namespace App\Auth;

class Auth {

	public function  __construct($container) {
	
		$this->container = $container;
	
	}

	public function __get($property) {

		if($this->container->{$property}) {

			return $this->container->{$property};

		}
	
	}

	public function user() {

		//return json_decode(json_encode(reset($this->user->getUser($_SESSION['user']['_id']))), true);

		return reset($this->user->getUser($_SESSION['user']['_id']));

	}

	public function lastLogin() {

		return $_SESSION['user']['lastLogin'];

	}


	public function initials() {

		$n = substr(reset($this->user->getUser($_SESSION['user']['_id']))->name, 0, 1);
		$s = substr(reset($this->user->getUser($_SESSION['user']['_id']))->surname, 0, 1);
	
		return strtoupper($n.$s);

	}

	public function userType() {

		return reset($this->user->getUser($_SESSION['user']['_id']))->type;

	}

	public function isAdmin() {

		if(reset($this->user->getUser($_SESSION['user']['_id']))->type == 0) return true;
		else return false;

	}

	
	public function check() {
	
		return isset($_SESSION['user']);

	}

	public function attemp($email, $password) {

		$user = reset($this->user->getUser($email));

		if(!$user) {

			return false;

		}

		if(password_verify($password, $user->crypPassword) && ($user->status == 1)) {

			$_SESSION['user'] = json_decode(json_encode(reset($this->user->getUser($email))), true);
			
			$this->user->updateLastLogin($email);

			return true;

		}

		return false;

	}

	public function matchesPassword($pwd) {

		if(password_verify($pwd, $_SESSION['user']['crypPassword'])) return true;
		else return false;

	}


	public function logout() {

		unset($_SESSION['user']);
				
	}

}


