<?php

require __DIR__ . '/../bootstrap/app.php';

$cj_init_time = date("d-m-Y H:i:s");

$all_projects = $container->projects->getListOfProjects([], ['projection' => ['_id' => 1, 'folder' => 1, 'email' => 1, 'uploadDate' => 1]]);

// get samples _id's
$samples = [];
foreach($container->global['sample'] as $gsample) {
	$samples[] = $gsample[0];
}

// put in list1 all the id's that:
// 1) are not samples
// 2) are older than 90 days (uploadDate)
$list1 = [];
// put in list2 all the id's that:
// 1) are not samples
// 2) are 85 days old
$list2 = [];
foreach($all_projects as $project) {
	if(!in_array($project->_id, $samples)) {
		$now =  new \DateTime();

		$ud = $project->uploadDate->toDateTime();

		if($now->diff($ud)->days >= $container->global['cron']['expiration']) {
			$p = [];
			$p["id"] = $project->_id;
			$p["folder"] = $project->folder;
			$list1[] = $p;
			$container->projects->deleteCompleteProject($project->_id);
		}

		if(($now->diff($ud)->days == $container->global['cron']['warn-mail']) && ($project->email != "")) {
			$p = [];
			$p["id"] = $project->_id;
			$list2[] = $p;

			$container->postProcessController->warnUser($project->_id);
		}

	}
}

$cj_end_time = date("d-m-Y H:i:s");

$logArray = [
	"init" => $cj_init_time,
	"end" => $cj_end_time,
	"mails" => sizeof($list2),
	"items" => sizeof($list1)
];

if(!empty($list1)) $logArray["removed"] = $list1;

$container->logger->info("CRON - Cronjob cleaning", $logArray);
