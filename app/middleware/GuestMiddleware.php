<?php

namespace App\Middleware;

class GuestMiddleware extends Middleware {

	public function __invoke($request, $response, $next) {

		if($this->auth->check()) {

			return $response->withRedirect($this->global['baseURL'].'workspace');

		}
	
		$response = $next($request, $response);

		return $response;

	}


}
