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
		'scriptsGlobals' => '/path/to/Scripts/',
		'scriptsPath' => '/path/to/Scripts/MCDNA/',
		'analysisPath' => '/path/to/Scripts/Analysis/',

		'ga4' => 'XXXX',

		'affinity' => [
			'script' => 'Rscript %sElasticEnergy/MCDNA_comp_protein_affinity_along_seq_WEB.R %s %s %s %s %s %s',
			'param1' => '/path/to/parmbsc1_naflex_Data/NA_Analysis/scripts/CurvesPDBs/Complexes/Jurgen',
			'param2' => '/path/to/Scripts/ElasticEnergy/stif_bsc1_meth_hydr_bsc1_dimer.dat',
			'param3' => '/path/to/Scripts/ElasticEnergy/stif_bsc1_meth_hydr_bsc1_tet.dat',
		],
		
		'sge' => [
			'qsub' => '/path/to/app/SGE/qsub ',
			'qstat' => '/path/to/app/SGE/qstat ',
		 	'qdel' => '/path/to/app/SGE/qdel ' 
		],
	]

];
