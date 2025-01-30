<?php
namespace App\Controllers\Auth;

use App\Controllers\Controller;
use App\Models\User;
use Respect\Validation\Validator as v;

class PasswordController extends Controller {

	private function generateStaticPage($r, $t, $d, $b, $i = null) {

		$vars = [
			'page' => [
				'title' => $t,
				'description' => $d,
				'basename' => $b
			],
			'id' => $i,
		];  

		$this->view->render($r, 'auth/'.$b.'.html', $vars);

	}

	// create reset password form
	public function getResetPwd($request, $response, $args) {
		
		$this->generateStaticPage($response, 'Reset Password - '.$this->global['longProjectName'], 'Reset your password for '.$this->global['longProjectName'], 'reset', $args['id']);
		
	}

	// process reset password
	public function postResetPwd($request, $response) {

		// validate if correct data in form
		$validationArray = [
			
				'email' => v::noWhitespace()->notEmpty()->email(),
				'password1' => v::noWhitespace()->notEmpty(),

		];

		// password matches!!
		if($request->getParam('password1') != $request->getParam('password2')) {
			
			$validation = $this->validator->validate($request, $validationArray);

			$_SESSION['errors']['password2'][] = 'Both password fields must be equal.';

			$this->flash->addMessage('error', 'Please check your errors.');

			$this->logger->error("WEB - Reset password: passwords not matching", [$request->getParam('email'), $request->getParam('password1'), $request->getParam('password2')]);
			return $response->withRedirect($this->global['baseURL'].'auth/reset-password/'.$request->getParam('id'));

		}

		// compare hash with email -> ERROR
		if(!password_verify($request->getParam('email'), $request->getParam('id'))) {
			
			$validation = $this->validator->validate($request, $validationArray);

			$_SESSION['errors']['email'][] = 'Email incorrect.';

			$this->flash->addMessage('error', 'Please check your errors.');

			$this->logger->error("WEB - Reset password: email incorrect", [$request->getParam('email'), $request->getParam('id')]);
			return $response->withRedirect($this->global['baseURL'].'auth/reset-password/'.$request->getParam('id'));

		}

		// email not exists -> ERROR
		if(!$this->user->userExists($request->getParam('email'))) {

			$validation = $this->validator->validate($request, $validationArray);

			$_SESSION['errors']['email'][] = 'Email unexisting';

			$this->flash->addMessage('error', 'The email provided doesn\'t exist in our Database.');

			$this->logger->error("WEB - Reset password: email unexisting", [$request->getParam('email')]);
			return $response->withRedirect($this->global['baseURL'].'auth/reset-password/'.$request->getParam('id'));

		}

		// validate if correct data in form
		$validation = $this->validator->validate($request, $validationArray);

		if($validation->failed()) {

			$this->flash->addMessage('error', 'Please check your errors.');

			$this->logger->error("WEB - Reset password: incorrect form data", [$request->getParsedBody()]);
			return $response->withRedirect($this->global['baseURL'].'auth/reset-password/'.$request->getParam('id'));
		
		}


		// change password to DB
		$this->user->updateUser($request->getParam('email'), ['crypPassword' => password_hash($request->getParam('password1'), PASSWORD_DEFAULT)]);
	
		$this->flash->addMessage('success', 'Your password has been updated, please sign in.');
		unset($_SESSION['old']);
	
		return $response->withRedirect($this->global['baseURL'].'auth/signin');
			
	}


	// create my profile form
	public function getChangeProfile($request, $response) {

		$countries = $this->countries->getCountriesList();

		$this->generateStaticPage($response, 'My Profile - '.$this->global['longProjectName'], 'Change your password and personal data for the platform '.$this->global['longProjectName'], 'change', $countries);

	}

	// process change password
	public function postChangeUser($request, $response) {

		$validationArray = [
			
			'name' => v::notEmpty(),
			'surname' => v::notEmpty(),
			'institution' => v::notEmpty(),
			'country' => v::notEmpty(),

		];

		// validate if correct data in form
		$validation = $this->validator->validate($request, $validationArray);

		if($validation->failed()) {

			$this->flash->addMessage('error', 'Please provide correct data.');

			$this->logger->error("WEB - Change user data: incorrect form data", [$request->getParsedBody()]);
			return $response->withRedirect($this->global['baseURL'].'auth/profile');
		
		}

		// creation of user in DB
		$this->user->updateUser($_SESSION['user']['_id'], [
			
			'name' => ucfirst($request->getParam('name')),
			'surname' => ucfirst($request->getParam('surname')),
			'institution' => ucfirst($request->getParam('institution')),
			'country' => $request->getParam('country'),

		]);

		$this->flash->addMessage('success', 'Profile data successfully changed');

		return $response->withRedirect($this->global['baseURL'].'auth/profile');

	}

	// process change password
	public function postChangePassword($request, $response) {

		$validationArray = [
			
			'oldpassword' => v::noWhitespace()->notEmpty(),
			'password1' => v::noWhitespace()->notEmpty(),
			'password2' => v::noWhitespace()->notEmpty()

		];

		// password matches!!
		if($request->getParam('password1') != $request->getParam('password2')) {
			
			$validation = $this->validator->validate($request, $validationArray);

			$_SESSION['errors']['password2'][] = 'Both password fields must be equal.';

			$this->flash->addMessage('error', 'Please check your errors.');

			$this->logger->error("WEB - Change password: passwords not matching", [$_SESSION['user']['_id'], $request->getParam('password1'), $request->getParam('password2')]);
			return $response->withRedirect($this->global['baseURL'].'auth/profile#tab_1_3');

		}


		// validate if password matches
		if(!$this->auth->matchesPassword($request->getParam('oldpassword'))) {
			
			$validation = $this->validator->validate($request, $validationArray);

			$_SESSION['errors']['oldpassword'][] = "Password doesn't match";

			$this->flash->addMessage('error', 'Please verify your data!!');

			$this->logger->error("WEB - Change password: old password doesn't match", [$_SESSION['user']['_id'], $request->getParam('password1'), $request->getParam('password2'), $request->getParam('oldpassword')]);
			return $response->withRedirect($this->global['baseURL'].'auth/profile#tab_1_3');

		}


		// validate if correct data in form
		$validation = $this->validator->validate($request, $validationArray);

		if($validation->failed()) {

			$this->flash->addMessage('error', 'Please provide correct data.');

			$this->logger->error("WEB - Change password: incorrect form data", [$request->getParsedBody()]);
			return $response->withRedirect($this->global['baseURL'].'auth/profile#tab_1_3');
		
		}

		// changing user password in DB
		$newCrypPass = password_hash($request->getParam('password1'), PASSWORD_DEFAULT);

		$this->user->updateUser($_SESSION['user']['_id'], ['crypPassword' => $newCrypPass]);

		$_SESSION['user']['crypPassword'] = $newCrypPass;

		$this->flash->addMessage('success', 'Password successfully changed');

		return $response->withRedirect($this->global['baseURL'].'auth/profile#tab_1_3');



	}


}

