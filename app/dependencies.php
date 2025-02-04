<?php

use Respect\Validation\Validator as v;

// Get the container
$container = $app->getContainer();


// it must be declared here because it's passed as a global to the view below
$container['auth'] = function($c) {

	return new \App\Auth\Auth($c);

};

$container['flash'] = function($c) {

	return new \Slim\Flash\Messages;

};


// Twig view dependency
$container['view'] = function ($c) {

	$cf = $c->get('settings')['view'];
	$view = new \Slim\Views\Twig($cf['path'], $cf['twig']);
	$view->addExtension(new \Slim\Views\TwigExtension(
			$c->router,
			$c->request->getUri()
	));
	
	// global vars for twig
	$view->getEnvironment()->addGlobal('ga4', $c->get('settings')['ga4']);
	$view->getEnvironment()->addGlobal('baseURL', $c->get('settings')['baseURL']);	
	$view->getEnvironment()->addGlobal('sampleOutputs', $c->get('globals')['sample']);
	$view->getEnvironment()->addGlobal('longName', $c->get('globals')['longProjectName']);
	/*$view->getEnvironment()->addGlobal('auth', [
		'check' => $c->auth->check(),
		'user' => $c->auth->user(),
		'isAdmin' => $c->auth->isAdmin(),
		'lastLogin' => $c->auth->lastLogin(),
		'initials' => $c->auth->initials(),
	]);*/
	$view->getEnvironment()->addGlobal('flash', $c->flash);

	return $view;
};


//Override the default Not Found Handler
$container['notFoundHandler'] = function ($c) {
	return function ($request, $response) use ($c) {
			$vars = [
				'page' => [
				'title' => 'MC DNA - Page not found!',
				'description' => 'The page you\'re requesting doesn\'t exist',
				'basename' => 'error'
				],
			]; 
			$c->logger->error("WEB - Page not found", [$request->getUri()->getPath()]);	
			return $c->view->render($response->withStatus(404), '404.html',$vars);
	};
};


// monolog
$container['logger'] = function ($c) {
	$settings = $c->get('settings')['logger'];
	$logger = new Monolog\Logger($settings['name']);
	$logger->pushProcessor(new Monolog\Processor\UidProcessor());
	//$logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
	$logger->pushHandler(new Monolog\Handler\RotatingFileHandler($settings['path'], 30, $settings['level']));
	return $logger;
};


// DB dependency
$container['db'] = function ($c) {
	
	$db = $c->get('settings')['db'];
	
	$mng = new \MongoDB\Driver\Manager("mongodb://".$db['username'].":".$db['password']."@".$db['host']."/".$db['database']);

	// testing if mongo is working
	/*try {
		$mng->executeCommand('test', new \MongoDB\Driver\Command(['ping' => 1]));
	} catch(\MongoDB\Driver\Exception\ConnectionException $e) {
		throw new \Exception("Unable to found Mongo server", 503); 
	}*/

	return new \App\Models\DB($mng, $db['database']);

};


// MAILER
$container['mailer'] = function ($c) {

	$mailer = new PHPMailer;
	
	$mailer->CharSet = 'UTF-8';
	$mailer->IsSMTP();
	$mailer->SMTPDebug = 0;
	$mailer->Host = $c->get('settings')['mail']['host'];
	$mailer->SMTPAuth = true;
	$mailer->SMTPSecure = 'ssl';
	$mailer->Port =  $c->get('settings')['mail']['port'];
	$mailer->Username = $c->get('settings')['mail']['login'];
	$mailer->Password = $c->get('settings')['mail']['password'];
	$mailer->isHTML(true);

	return new App\Models\Mailer($c->view, $mailer);

};


// CSRF
$container['csrf'] = function ($c) {
	
	return new \Slim\Csrf\Guard;

};


// MODELS
$container['user'] = function($c) {

	return new \App\Models\User($c);

};

$container['utils'] = function($c) {

	return new \App\Models\Utilities($c);

};

$container['countries'] = function($c) {

	return new \App\Models\Countries($c);

};

$container['files'] = function($c) {

	return new \App\Models\Files($c);

};

$container['meta'] = function($c) {

	return new \App\Models\Meta($c);

};

$container['sge'] = function($c) {

	return new \App\Models\ProcessSGE($c);

};

$container['projects'] = function($c) {

	return new \App\Models\Projects($c);

};





// CONTROLLERS
$container['staticPages'] = function($c) {

	return new \App\Controllers\StaticPagesController($c);

};

$container['uploadController'] = function($c) {

	return new \App\Controllers\UploadController($c);

};

$container['authController'] = function($c) {

	return new \App\Controllers\Auth\AuthController($c);

};

$container['passwordController'] = function($c) {

	return new \App\Controllers\Auth\PasswordController($c);

};

$container['wsController'] = function($c) {

	return new \App\Controllers\WSController($c);

};

$container['outputController'] = function($c) {

	return new \App\Controllers\OutputController($c);

};

$container['postProcessController'] = function($c) {

	return new \App\Controllers\PostProcessController($c);

};

$container['adminController'] = function($c) {

	return new \App\Controllers\AdminController($c);

};



// VALIDATION
$container['validator'] = function($c) {

	return new \App\Validation\Validator;

};


// MIDDLEWARE
$app->add(new \App\Middleware\CorsMiddleware);
//$app->add(new \App\Middleware\HttpsMiddleware($container));
$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));
//$app->add(new \App\Middleware\CsrfViewMiddleware($container));
//$app->add($container->csrf);


//GLOBALS
$container['global'] = function($c) {
	
	return $c->get('globals');

};

//HANDLERS
$container['errorHandler'] = function ($c) {

    return new \App\Handlers\Error($c['logger'], $c);

};

