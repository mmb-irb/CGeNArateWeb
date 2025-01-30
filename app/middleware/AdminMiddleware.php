<?php

namespace App\Middleware;

class AdminMiddleware extends Middleware {

	public function __invoke($request, $response, $next) {


		if($this->auth->userType() != 0) {

			return $response->withRedirect($this->global['baseURL'].'workspace');

		}
	
		$response = $next($request, $response);

		return $response;

	}


}
