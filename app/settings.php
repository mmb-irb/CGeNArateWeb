<?php

return [

	'globals' => [
		'shortProjectName' => 'CGN',
		'longProjectName' => 'CGeNArateWeb',
		'filesPathName' => 'data',
		'analysisPathName' => 'ANALYSIS',
		'filesPath' =>  __DIR__ . '/../public/data/',
		'diskQuota' => 52428800, // 50MB per user
		'totalDisk' => 107374182400, // 100 GB total disk
		'fromName' => 'CGeNArateWeb tool',
		'uploads' => 'uploads',
		'roles' => [0 => "Admin", 1 => "Premium", 2 => "Common", 100 => "Premium Rq", 101 => "Premium Rj"],
		'rolesColor' => [0 => "green", 1 => "grey-cascade", 2 => null, 100 => "red-haze", 101 => "purple-plum"],
		'wsTimeOut' => 5000,
		'defaultFlex' => 'contacts',

		// outputs
		'summary' => [
			'strPDB' => 'output_schnarp/str.pdb',
			'trajPDB' => 'output_schnarp/display/traj.pdb',
			'trajDCD' => 'output_schnarp/display/traj.dcd',
			'sumFileDown' => 'mcdna.tgz',
			'anFileDown' => 'mcdna.analysis.tgz'
		],

		// sample outputs
		'sample' => [
			'1' => ['67d9b855102df1.14756966', 'CGeNArate - Coarse Grain'],
			'2' => ['67d9b89286d769.61804502', 'CGeNArate - Atomistic'],
			'3' => ['67d9bc12443dc0.80753534', 'Circular CGeNArate - Coarse Grain'],
			'4' => ['67d9bc14bdaf24.23726253', 'Circular CGeNArate - Atomistic'],
			'5' => ['67d9bfc0534745.72291737', 'CGeNArate + Proteins  - Coarse Grain'],
			'6' => ['67d9bfc8b5dd99.99963149', 'CGeNArate + Proteins - Atomistic']
		],

		'flex' => [
			'foldereq' => '/EQ_%s/ANALYSIS/',
			'foldertraj' => '/TRAJ_%s/ANALYSIS/'
		],

		'NAFlex' => [
			'naflexeq' => 'NAFlex/PDB/',
			'naflextraj' => 'NAFlex/'
		],

		'contacts' => [
			'foldereq' => 'CONTACTS/',
			'foldertraj' => 'CONTACTS/'
		],

		'pcazip' => [
			'folder' => 'PCAZIP/'
		],

		'stiffness' => [
			'folder' => 'STIFFNESS/FORCE_CTES/'
		],

		'curves' => [
			'folder' => 'CURVES/'
		],

		'bending' => [
			'folder' => 'Bending/'
		],

		'circular' => [
			'folder' => 'Circular/'
		],

		'energy' => [
			'folder' => 'ElasticEnergy/'
		],

		'end-to-end' => [
			'folder' => 'END-TO-END/',
			'pl' => 'PersistenceLength'
		],

		'sasa' => [
			'folder' => 'Sasa/'
		],

		'flextypes' => ['', 'curves', 'stiffness', 'pcazip', 'contacts', 'bending', 'circular', 'energy', 'end-to-end', 'sasa'],

		// queues
		'sh' => [
			'queuename' => 'local.q',

			'plbase' => 'perl %srunMCDNA_all_new.pl %s/%s %s %s %s'."\n\n",
			'plcirc' => 'perl %srunMCDNA_circular_all.pl %s/%s %s 5 %s %s %s %s'."\n\n",
			'plprot' => 'perl %srunMCDNA_Prots_all.pl %s/%s %s 3 %s %s %s'."\n\n",
			'plchrdyn' => 'perl %srunChromatinDynamics.pl %s/%s %s/%s %s %s'."\n\n",

			'rtraj' => 'Rscript %sMuG_DNA_bending_ensemble.R 10 .'."\n",
			'rstr' => 'Rscript %sMuG_DNA_bending_single_structure.R .'."\n",

			'analysis' => 'perl %srunMCDNA_Analysis.pl %s %s %s'."\n\n",

			'end' => 'wget -q -O- "%send/jobs/%s"'."\n"
		],

		'input' => [
			'tools' => [1 => "CGeNArate", 2 => "Circular CGeNArate", 3 => "CGeNArate + Proteins"],
			//'tools' => [1 => "CGeNArate", 2 => "Circular CGeNArate"/*, 3 => "CGeNArate + Proteins"*/],
			'resolution' => [0 => "Coarse Grain", 1 => "Atomistic"],
			'types' => ["unimodel" => "UniModel", "multimodel" => "MultiModel"],
			'operations' => ["createStructure" => "Create Structure", "createTrajectory" => "Create Trajectory"],
			'numstructures' => [ 100, 100, 100,	10,	10,	50,	50 ],
			'samplesequence' => [
					"GATTACATACATACAGATTACATACATACAGATTACATACATACAGATTACATACATACAGATTACATACATACAGATTACATACATACA",
					"GATTACATACATACAGATTACATACATACA",
					"GATTACATACATACAGATTACATACATACA",
					"TCTCTCTCTCTCTCTCTTAAAGGTATACAAGAAAGTTTGTTGGTCTTTTTACCTTCCCGTTTCGCTCCAAGTTAGTATAAAAAAGCTGAACGAG",
					"TCTCTCTCTCTCTCTCTTAAAGGTATACAAGAAAGTTTGTTGGTCTTTTTACCTTCCCGTTTCGCTCCAAGTTAGTATAAAAAAGCTGAACGAG",
					"GATTACATACATACAGATTACATACATACAGATTACATACATACAGATTACATACATACAGATTACATACATACAGATTACATACATACA",
					"GATTACATACATACAGATTACATACATACAGATTACATACATACAGATTACATACATACAGATTACATACATACAGATTACATACATACA"
			],
			'samplenucleosomes' => ["10 15 23 53 82"],
			'sampleprot' => [
				[ "code" => "1vfc", "length" => 12, "position" => 3 ],
				[ "code" => "1wtr", "length" => 7, "position" => 30 ],
				[ "code" => "3zhm", "length" => 8, "position" => 60 ],
				[ "code" => "1bnz", "length" => 7, "position" => 80 ]
			]	
		],

		'cron' => [
			'expiration' => 20,
			'warn-mail' => 15
		]
 
	]

];
