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
			'path' => '/path/to/logs/',
			'level' => \Monolog\Logger::DEBUG,
		],
		'db' => [
			'host'     => 'XXXX',
			'database' => 'XXXX',
			'username' => 'XXXX',
			'password' => 'XXXX'
		],
		'mail' => [
			'login' => 'XXXX',
			'password' => 'XXXX',
			'host' => 'XXXX',
			'port' => 465
		],

		'absoluteURL' => 'https://mmb.irbbarcelona.org/CGeNArate/', 
		'absoluteURLMail' => 'https://mmb.irbbarcelona.org/CGeNArate/',
		'baseURL' => '/CGeNArate/',
		'diskPath' => '/path/to/Web/',
		'wfDataPath' => '/path/to/data/inside/wf/docker/',
		'scriptsGlobals' => '/path/to/Scripts/',
		'scriptsPath' => '/path/to/Scripts/MCDNA/',
		'analysisPath' => '/path/to/Scripts/Analysis/',

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
	]

];
