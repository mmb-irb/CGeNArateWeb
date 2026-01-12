<?php

return [

	'settings' => [
		'displayErrorDetails' => true,
		'view' => [
			'path' => __DIR__ . '/../resources/views',
			'twig' => [
				'cache' => false
			]
		],
    'logger' => [
			'name' => 'CGeNArateWeb',
			'path' => __DIR__ . '/../logs/app.log',
			'level' => \Monolog\Logger::DEBUG,
		],
		'db' => [
			'host'     => 'XXXX',
			'database' => 'XXXX',
			'username' => 'XXXX',
			'password' => 'XXXX',
			'port'     => 'XXXX'
		],
		'mail' => [
			'login' => 'XXXX',
			'password' => 'XXXX',
			'host' => 'XXXX',
			'port' => 'XXXX'
		],

		'absoluteURL' => 'https://mmb.irbbarcelona.org/CGNAW/', 
		'absoluteURLMail' => 'https://mmb.irbbarcelona.org/CGNAW/',
		'containerURL' => 'http://cgnaw_stack_cgnaw_website:3001/CGNAW/',
		'baseURL' => '/CGNAW/',
		'diskPath' => 'XXX',
		'wfDataPath' => 'XXX',
		'scriptsGlobals' => 'XXX',
		'scriptsLocal' => 'XXX',
		'mailTemplates' => 'XXX',
		'scriptsPath' => 'XXX',
		'analysisPath' => 'XXX',
		'ga4' => 'XXXX',

		'affinity' => [
			'script' => 'Rscript /scripts/MCDNA/ElasticEnergy/MCDNA_comp_protein_affinity_along_seq_WEB.R %s %s %s %s %s %s',
			'param1' => '/scripts/MCDNA/ElasticEnergy/Jurgen',
			'param2' => '/scripts/MCDNA/ElasticEnergy/stif_bsc1_meth_hydr_bsc1_dimer.dat',
			'param3' => '/scripts/MCDNA/ElasticEnergy/stif_bsc1_meth_hydr_bsc1_tet.dat',
		],
		
		'sge' => [
			'host' => 'XXXXXX',
			'cpus' => '1.00',
			'mem' => '1G',
			'qsub' => 'ssh -p 222 -o StrictHostKeyChecking=no www-data@%s "qsub %s"',
			'qstat' => 'ssh -p 222 -o StrictHostKeyChecking=no www-data@%s "qstat -u www-data | grep \"^%s\""',
		 	'qdel' => 'ssh -p 222 -o StrictHostKeyChecking=no www-data@%s "qdel %s"' 
		],

		'docker' => [
			'launch' => 'python3 %slaunch-worker.py --command "%s" --volumes \'%s\'',
			'volumes' => [
				($_ENV["HOST_SCRIPTS_PATH"]) => ['bind' => '/app/Scripts', 'mode' => 'ro'],
				($_ENV["HOST_DATA_PATH"]) => ['bind' => '/mnt', 'mode' => 'rw'],
			],
			'status' => 'python3 %sget-job-status.py %s',
			'stop' => 'python3 %sstop-job.py %s'
		],
	]

];
