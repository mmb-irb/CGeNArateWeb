<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\CheckDBMiddleware;
//use App\Middleware\AdminMiddleware;

// Creating routes

$app->group('', function() use ($container) {

	// HELP ROUTES
	// 
	$this->get('/help/method', 'staticPages:method');
	$this->get('/help/inputs', 'staticPages:inputs');
	$this->get('/help/outputs', 'staticPages:outputs');
	/*$this->get('/help/analysis/curves', 'staticPages:curves');
	$this->get('/help/analysis/stiffness', 'staticPages:stiffness');
	$this->get('/help/analysis/pcazip', 'staticPages:pcazip');
	$this->get('/help/analysis/contacts', 'staticPages:contacts');
	$this->get('/help/analysis/bending', 'staticPages:bending');
	$this->get('/help/analysis/circular', 'staticPages:circular');
	$this->get('/help/analysis/energy', 'staticPages:energy');
	$this->get('/help/analysis/end-to-end', 'staticPages:endtoend');
	$this->get('/help/analysis/sasa', 'staticPages:sasa');*/

	// INPUT  / UPLOAD
	//
	$this->get('/input[/sample/{sampletype}]', 'uploadController:input');
	$this->get('/affinity', 'uploadController:affinity');
	$this->post('/upload', 'uploadController:upload');

	// common output
	$this->get('/output/summary/{id}[/{sample}]', 'outputController:outputSummary');
	$this->get('/output/flex/{strtype}/contacts/{id}[/{sample}]', 'outputController:outputFlexContacts');
	$this->get('/output/flex/{strtype}/pcazip/{id}[/{sample}]', 'outputController:outputFlexPCAZip');
	$this->get('/output/flex/{strtype}/stiffness/{id}[/{sample}]', 'outputController:outputFlexStiffness');
	$this->get('/output/flex/{strtype}/curves/{id}[/{sample}]', 'outputController:outputFlexCurves');
	$this->get('/output/flex/{strtype}/circular/{id}[/{sample}]', 'outputController:outputFlexCircular');
	$this->get('/output/flex/{strtype}/bending/{id}[/{sample}]', 'outputController:outputFlexBending');
	$this->get('/output/flex/{strtype}/end-to-end/{id}[/{sample}]', 'outputController:outputFlexEndToEnd');
	$this->get('/output/flex/{strtype}/energy/{id}[/{sample}]', 'outputController:outputFlexEnergy');
	$this->get('/output/flex/{strtype}/sasa/{id}[/{sample}]', 'outputController:outputFlexSASA');
	$this->get('/backoutput/flex/{id}/{strtype}/{section}/{subsection}[/{type}]', 'outputController:getSpecificOutput');
	//$this->get('/backoutput/flex/{id}/{strtype}/{section}/{subsection}[/{type:.*}]', 'outputController:getSpecificOutput');

	$this->post('/path/for/{id}', 'outputController:getPath');

	$this->post('/get/status/{id}', 'outputController:getStatus');

	$this->get('/progress/for/{id}', 'outputController:drawProgress');

	// DOWNLOAD FILE
	//
	$this->get('/download/file/{kind}/{id}', 'outputController:downloadFile');

	// POST PROCESS
	//
	$this->get('/end/jobs/{id}', 'postProcessController:endOfJobs');
	$this->get('/cron/autoclean', 'postProcessController:autoClean');
	$this->get('/warning/mail/{id}', 'postProcessController:sendWarnUser');

	// JS GLOBALS
	//
	$this->get('/js/globals', 'outputController:JSGlobals');

	// COOKIES
	//
	$this->get('/cookies', 'staticPages:cookies');

	// HOME ROUTE
	// 
	$this->get('/', 'staticPages:home');

	// routes where the auth users can't access
	/*$this->group('', function() use ($container) {

		// HOME ROUTE
		// 
		$this->get('/', 'staticPages:home');

		// SIGN UP ROUTE
		// 
		//$this->get('/auth/signup', 'authController:getSignUp')->add(new \App\Middleware\CsrfViewMiddleware($container))->add($container->csrf);
		//$this->post('/auth/signup', 'authController:postSignUp')->add($container->csrf);

		// FORGOT PASSWORD ROUTE
		// 
		//$this->get('/auth/forgot-password', 'authController:getForgotPwd')->add(new \App\Middleware\CsrfViewMiddleware($container))->add($container->csrf);
		//$this->post('/auth/forgot-password', 'authController:postForgotPwd')->add($container->csrf);

		// RESET PASSWORD ROUTE
		// 
		//$this->get('/auth/reset-password/{id:.*}', 'passwordController:getResetPwd')->add(new \App\Middleware\CsrfViewMiddleware($container))->add($container->csrf);
		//$this->post('/auth/reset-password', 'passwordController:postResetPwd')->add($container->csrf);

		// SIGN IN ROUTE
		// 
		//$this->get('/auth/signin', 'authController:getSignIn')->add(new \App\Middleware\CsrfViewMiddleware($container))->add($container->csrf);
		//$this->post('/auth/signin', 'authController:postSignIn')->add($container->csrf);

	})->add(new GuestMiddleware($container));*/



	// routes where the non auth users can't access
	/*$this->group('', function() use ($container) {

		// SIGN OUT ROUTE
		// 
		$this->get('/auth/signout', 'authController:getSignOut');

		// LOCK SCREEN ROUTE
		// 
		$this->get('/auth/lock', 'authController:getLockScreen')->add(new \App\Middleware\CsrfViewMiddleware($container))->add($container->csrf);

		// CHANGE PASSWORD ROUTE
		// 
		$this->get('/auth/profile', 'passwordController:getChangeProfile')->add(new \App\Middleware\CsrfViewMiddleware($container))->add($container->csrf);
		$this->post('/auth/profile/user', 'passwordController:postChangeUser')->add($container->csrf);
		$this->post('/auth/profile/password', 'passwordController:postChangePassword')->add($container->csrf);

		// WORKSPACE
		// 
		$this->get('/workspace', 'wsController:workspace');

		$this->get('/tree/{id}', 'wsController:getTree');
		
		// GET DIR CONTENT
		//
		$this->get('/dir/{id}', 'wsController:getTable');
		
		// DOWNLOAD FOLDER
		//
		$this->get('/download/folder/{id}', 'wsController:downloadFolder');

		// DOWNLOAD FILE
		//
		//	$this->get('/download/file/{id}', 'wsController:downloadFile');

		// OPEN FILE
		//
		$this->get('/open/file/{id}', 'wsController:openFile');

		// DELETE FILE
		//
		$this->get('/delete/file/{id}', 'wsController:deleteFile');

		// DELETE FOLDER
		//
		$this->get('/delete/folder/{id}', 'wsController:deleteFolder');

		// GET META
		//
		$this->get('/get/meta/{id}', 'wsController:getMetaInfo');

		// GET FILE PATH
		//
		$this->get('/get/path/{id}', 'wsController:getFilePath');

		// CHECK FINISHED JOBS
		//
		$this->get('/check/jobs', 'wsController:checkFinishedJobs');

		// ADMIN GROUP
		//
		//$this->get('/js/globals', 'adminController:JSGlobals');
		$this->get('/admin/dashboard', 'adminController:dashboard')->add(new AdminMiddleware($container));
		$this->get('/admin/change/type/{id}/{ntype}/{otype}', 'adminController:changeTypeOfUser')->add(new AdminMiddleware($container));
		$this->get('/admin/change/status/{id}/{status}', 'adminController:changeStatusOfUser')->add(new AdminMiddleware($container));
		$this->post('/admin/modify/user', 'adminController:modifyUserData')->add(new AdminMiddleware($container));
		$this->get('/admin/delete/user/{id}', 'wsController:deleteUser')->add(new AdminMiddleware($container));


	})->add(new AuthMiddleware($container));*/

})->add(new CheckDBMiddleware($container));