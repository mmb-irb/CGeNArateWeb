<?php

namespace App\Middleware;

class HttpsMiddleware extends Middleware {

	public function __invoke($request,  $response, $next) {
		if ($request->getUri()->getScheme() !== 'https') {
        $uri = $request->getUri()->withScheme("https")->withPort(null);
				//return $response->withRedirect( (string)'https://mmb.irbbarcelona.org/CGeNArate' );
				//var_dump($uri);
				return $response->withRedirect("https://mmb.irbbarcelona.org/CGeNArate/");
    } else {
				return $next($request, $response);
		}
	}

}
