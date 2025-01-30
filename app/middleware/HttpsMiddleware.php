<?php

namespace App\Middleware;

class HttpsMiddleware extends Middleware {

	public function __invoke($request,  $response, $next) {
		if ($request->getUri()->getScheme() !== 'https') {
        $uri = $request->getUri()->withScheme("https")->withPort(null);
				//return $response->withRedirect( (string)'https://mmb.irbbarcelona.org/MCDNA' );
				//var_dump($uri);
				return $response->withRedirect("https://mmb.irbbarcelona.org/MCDNA/");
    } else {
				return $next($request, $response);
		}
	}

}
