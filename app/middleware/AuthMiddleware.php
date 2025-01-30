<?php

namespace App\Middleware;

class AuthMiddleware extends Middleware {

	public function __invoke($request, $response, $next) {
	
		if(!$this->auth->check()) {

			$_SESSION['requestedURI'] = $request->getUri()->getPath();

			$this->flash->addMessage('error', 'Please, sign in first.');
			
			return $response->withRedirect($this->global['baseURL'].'auth/signin');

		}

		unset($_SESSION['requestedURI']); 

		//if($request->getUri()->getPath() != 'auth/signout') $_SESSION['lastURI'] = $request->getUri()->getPath();

		// TODO: white list with the URIS we can save in the cookie (not tree/{id} /dir/{id} get/path/{id}, etc)
		//if(($request->getUri()->getPath() != 'auth/signout') && ($request->getUri()->getPath() != 'auth/lock')) setcookie('lastURI', $request->getUri()->getPath(), time() + (86400 * 30));  

		$response = $next($request, $response);
		return $response;
		
	}


}


