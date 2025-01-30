<?php
namespace App\Controllers\Auth;

use App\Controllers\Controller;
use Respect\Validation\Validator as v;

class AuthController extends Controller {

	private function generateStaticPage($r, $t, $d, $b, $c = null) {

		$vars = [
			'page' => [
				'title' => $t,
				'description' => $d,
				'basename' => $b
			],
			'countries' => $c,
		];  

		$this->view->render($r, 'auth/'.$b.'.html', $vars);

	}


	// create sign out page
	public function getSignOut($request, $response) {

		$this->auth->logout();

		return $response->withRedirect($this->global['baseURL']);
		
	}

	// create sign in form
	public function getSignIn($request, $response) {

		$this->generateStaticPage($response, 'Sign In - '.$this->global['longProjectName'], 'Sign In to '.$this->global['longProjectName'].', insert your data', 'signin');

	}

	// process sign in data
	public function postSignIn($request, $response) {

		// validate if correct data in form
		$validationArray = [
			
			'email' => v::noWhitespace()->notEmpty()->email(),
			'password' => v::noWhitespace()->notEmpty(),

		];

		$validation = $this->validator->validate($request, $validationArray);

		if($validation->failed()) {

			$this->flash->addMessage('error', 'Please check your errors.');
			
			$this->logger->error("WEB - Login error: login and / or password not provided", [$request->getParam('email'), $request->getParam('password')]);
			return $response->withRedirect($this->global['baseURL'].'auth/signin');
		
		}

		// make login
		$auth = $this->auth->attemp(
			$request->getParam('email'),
			$request->getParam('password')
		);

		if(!$auth) {

			$validation = $this->validator->validate($request, $validationArray);
			$_SESSION['errors']['email'][] = 'Email unexisting';

			$this->flash->addMessage('error', 'User not existing or login incorrect.');
			
			$this->logger->error("WEB - Login error: wrong user", [$request->getParam('email'), $request->getParam('password')]);
			return $response->withRedirect($this->global['baseURL'].'auth/signin');

		}

		$type = reset($this->user->getUser($_SESSION['user']['_id']))->type;

		if($type == 101) $this->flash->addMessage('warning', 'Your request to have a premium account has been rejected. You can continue enjoying the features of a common user.');

		if(isset($_SESSION['requestedURI'])) {

			return $response->withRedirect($this->global['baseURL'].$_SESSION['requestedURI']);

		}

		//if(isset($_SESSION['lastURI'])) {
		/*if(isset($_COOKIE['lastURI'])) {

			return $response->withRedirect($this->global['baseURL'].$_COOKIE['lastURI']);

		}*/

		return $response->withRedirect($this->global['baseURL'].'workspace');

	}

	// create forgot password form
	public function getForgotPwd($request, $response) {

		$this->generateStaticPage($response, 'Forgot Password - '.$this->global['longProjectName'], 'Reset your password for '.$this->global['longProjectName'], 'forgot');

	}

	private function getDomainURL() {

    $headers = apache_request_headers();
    if(isset($headers["X-Forwarded-Host"])) $domainURL = $headers["X-Forwarded-Host"];
    else $domainURL = $headers["Host"];

    return $domainURL;
  
	}

	// process forgot password
	public function postForgotPwd($request, $response) {

		// validate if correct data in form
		$validationArray = [
			
				'email' => v::noWhitespace()->notEmpty()->email()

		];

		$validation = $this->validator->validate($request, $validationArray);

		if($validation->failed()) {

			$this->flash->addMessage('error', 'Please check your errors.');
			
			$this->logger->error("WEB - Forgot password error: email not provided", [$request->getParam('email')]);
			return $response->withRedirect($this->global['baseURL'].'auth/forgot-password');
		
		}

		// email not exists -> ERROR
		if(!$this->user->userExists($request->getParam('email'))) {

			$validation = $this->validator->validate($request, $validationArray);

			$_SESSION['errors']['email'][] = 'Email unexisting';

			$this->flash->addMessage('error', 'The email provided doesn\'t exist in our Database.');

			$this->logger->error("WEB - Forgot password error: email unexisting", [$request->getParam('email')]);
			return $response->withRedirect($this->global['baseURL'].'auth/forgot-password');

		}

		$user = reset($this->user->getUser($request->getParam('email')));	
		$domainURL = $this->getDomainURL();
    $fromName = $this->global['fromName'];
		$hashId = password_hash($user->_id, PASSWORD_DEFAULT);
		$name = $user->name.' '.$user->surname;

		$this->mailer->send('mails/mail-new-password.html', ['id' => $hashId, 'domainURL' => $domainURL, 'name' => $name] , function($message) use ($user, $fromName){
	    $message->to($user->email);
      $message->subject('Bioactive Compounds reset password');
      $message->fromName($fromName);
    });

		// if mail successfully sent, show message and redirect to sign in
		$this->flash->addMessage('success', 'Message successfully sent. Please check your email.');
		unset($_SESSION['old']);
		
		return $response->withRedirect($this->global['baseURL'].'auth/signin');
		
	}


	// create sign up form
	public function getSignUp($request, $response) {
		
		$countries = $this->countries->getCountriesList();
		
		$this->generateStaticPage($response, 'Sign Up - '.$this->global['longProjectName'], 'Sign Up to '.$this->global['longProjectName'].', insert your data', 'signup', $countries);

	}

	private function generateNewWorkspace($id) {

		// create dir with $id name
		$idNewDir = $this->files->createDir($id, null, $id);

		if(!$idNewDir) {

			return false;

		}
		
		$readmefile = $this->files->copyFileByName($this->global['filesPath'], 'README.md', $idNewDir, $id);

		if(!$readmefile) {
		
			return false;

		}

		$this->files->updateRunningStatus($readmefile, false);

		return true; 

	}

	// process sign up data
	public function postSignUp($request, $response) {

		$validationArray = [
			
			'email' => v::noWhitespace()->notEmpty()->email(),
			'name' => v::notEmpty()/*->alpha()*/,
			'surname' => v::notEmpty()/*->alpha()*/,
			'institution' => v::notEmpty(),
			'country' => v::notEmpty(),
			'password1' => v::noWhitespace()->notEmpty(),

		];

		// password matches!!
		if($request->getParam('password1') != $request->getParam('password2')) {
			
			$validation = $this->validator->validate($request, $validationArray);

			$_SESSION['errors']['password2'][] = 'Both password fields must be equal.';

			$this->flash->addMessage('error', 'Please check your errors.');

			$this->logger->error("WEB - Sign Up: passwords not equal", [$request->getParam('password1'), $request->getParam('password2')]);
			return $response->withRedirect($this->global['baseURL'].'auth/signup');

		}

		// validate if user exists
		if($this->user->userExists($request->getParam('email'))) {
			
			$validation = $this->validator->validate($request, $validationArray);

			$_SESSION['errors']['email'][] = 'Email already taken';

			$this->flash->addMessage('error', 'User already registered, try with another email.');

			$this->logger->error("WEB - Sign Up: user already registered", [$request->getParam('email')]);
			return $response->withRedirect($this->global['baseURL'].'auth/signup');

		}

		// validate if correct data in form
		$validation = $this->validator->validate($request, $validationArray);

		if($validation->failed()) {

			$this->flash->addMessage('error', 'Please check your errors.');

			$this->logger->error("WEB - Sign Up: form errors", [$request->getParsedBody()]);
			return $response->withRedirect($this->global['baseURL'].'auth/signup');
		
		}

		// if user requests for premium, we put on premium requested
		if((int)$request->getParam('type') == 1) $type = 100;
		else $type = (int)$request->getParam('type');

		// creation of user in DB
		$idNewUser = $this->user->createUser([
			
			'_id' => $request->getParam('email'),
			'name' => ucfirst($request->getParam('name')),
			'surname' => ucfirst($request->getParam('surname')),
			'institution' => ucfirst($request->getParam('institution')),
			'country' => (int)$request->getParam('country'),
			'type' => $type,
			'email' => $request->getParam('email'),
			'crypPassword' => password_hash($request->getParam('password1'), PASSWORD_DEFAULT),

		]);

		if(!$this->generateNewWorkspace($idNewUser)) {

			//$this->user->deleteUser($request->getParam('email'));
			$this->user->deleteUserComplete($idNewUser);

			$this->flash->addMessage('error', 'There was an error generating your workspace, please try later.');

			$this->logger->error("WEB - Sign Up: error generating workspace");
			return $response->withRedirect($this->global['baseURL'].'auth/signup');
				
		}

		$this->flash->addMessage('info', 'Welcome to your workspace.');

		if($type == 100) $this->flash->addMessage('warning', 'Your request to have premium user privileges is being assessed. In the meantime you have the same features as the common users.');


		// sign in after create user
		$this->auth->attemp($request->getParam('email'), $request->getParam('password1'));

		$user = reset($this->user->getUser($request->getParam('email')));	
		$domainURL = $this->getDomainURL();
    $fromName = $this->global['fromName'];
		$name = $user->name.' '.$user->surname;

		$this->mailer->send('mails/mail-welcome.html', ['domainURL' => $domainURL, 'name' => $name] , function($message) use ($user, $fromName){
	    $message->to($user->email);
      $message->subject('Welcome to Bioactive Compounds');
      $message->fromName($fromName);
    });

		
		return $response->withRedirect($this->global['baseURL'].'workspace');

	}

	// create sign up form
	public function getLockScreen($request, $response) {

		if(!isset($_SESSION['user'])) {

			$this->flash->addMessage('error', 'Please sign in');
			
			return $response->withRedirect($this->global['baseURL'].'auth/signin');

		}
		
		unset($_SESSION['user']);
		
		$this->generateStaticPage($response, 'Lock Screen - '.$this->global['longProjectName'], $this->global['longProjectName'].' page has been locked.', 'lock');

	}
	
}

