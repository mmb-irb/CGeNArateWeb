<?php

$GLOBALS['settings'] = [
	'db' => [
		'host'     => 'ms1.mmb.pcb.ub.es',
		'database' => 'MCDNA_test',
		'username' => 'newDataLoader',
		'password' => 'mdbwany2015',
		'projectsCol' => 'projects'
	],

	'mail' => [
		'login' => 'tools.mmb@gmail.com',
		'password' => '_ToOlS17',
		'host' => 'smtp.gmail.com',
		'port' => 465
	],

	'sample' => [
		'1' => ['5a3cd6a32b9e45.88876421', 'MC DNA - Coarse Grain'],
		'2' => ['5a9d4e7bb01b31.44017553', 'MC DNA - Atomistic'],
		'3' => ['5a3d7be6e49fb7.88498829', 'Circular MC DNA - Coarse Grain'],
		'4' => ['5a3cee1ac4a6c4.69000433', 'Circular MC DNA - Atomistic'],
		'5' => ['5a3cd2d4106f78.82278285', 'MC DNA + Proteins  - Coarse Grain'],
		'6' => ['5a3c3972c7be71.61030885', 'MCDNA + Proteins - Atomistic']
	],

	'cron' => [
		'expiration' => 20,
		'warn-mail' => 15
	],

	'absoluteURL' => 'https://mmb.irbbarcelona.org/webdev/slim/MCDNA/public/',
	'fromName' => 'MC DNA tool',
	'diskPath' => '/orozco/services/MCDNA_dev/Web/',
	'serverPath' => '/var/www/html/webdev/slim/MCDNA/'

];
