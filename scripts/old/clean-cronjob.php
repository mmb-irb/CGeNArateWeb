<?php 

require __DIR__ . '/../vendor/autoload.php';

//namespace Scripts;

require __DIR__ . '/lib/settings.php';
require __DIR__ . '/lib/phpmailer/phpmailer/class.smtp.php';
require __DIR__ . '/lib/phpmailer/phpmailer/class.phpmailer.php';
require __DIR__ . '/lib/template.php';
require __DIR__ . '/lib/functions.php';

$logger = new \Monolog\Logger('MCDNA');
$logger->pushProcessor(new Monolog\Processor\UidProcessor());
$logger->pushHandler(new Monolog\Handler\RotatingFileHandler( __DIR__ . '/../logs/app.log', 30, \Monolog\Logger::DEBUG));


$today = date("d-m-Y");

echo "\n********************\n";
echo "* $today - JOB *\n";
echo "********************\n";

$projectsCol = 'projects';
$cj_init_time = date("d-m-Y H:i:s");

$all_projects = getDocuments($projectsCol, [], ['projection' => ['_id' => 1, 'folder' => 1, 'email' => 1, 'uploadDate' => 1]]);

// get samples _id's
$samples = [];
foreach($GLOBALS['settings']['sample'] as $gsample) {
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

		if($now->diff($ud)->days >= $GLOBALS['settings']['cron']['expiration']) {
			$p = [];
			$p["id"] = $project->_id;
			$p["folder"] = $project->folder;
			$list1[] = $p;
			//$list1[] = "ID: ". $project->_id ." / FOLDER: ". $project->folder;
			removeProject($projectsCol, $project->_id);
		}

		if(($now->diff($ud)->days == $GLOBALS['settings']['cron']['warn-mail']) && ($project->email != "")) {
			$p = [];
			$p["id"] = $project->_id;
			$list2[] = $p;
			//$list2[] = "ID: ". $project->_id;
			warnUser($projectsCol, $project->_id);
		}

	}
}

$cj_end_time = date("d-m-Y H:i:s");

echo "Init time: $cj_init_time\n";
echo "End time: $cj_end_time\n\n";
echo sizeof($list2)." warning mail sent.\n";
//if(!empty($list2)) printList($list2); 
echo "\n". sizeof($list1)." registers removed.\n";
//if(!empty($list1)) printList($list1);

$logArray = [
	"init" => $cj_init_time, 
	"end" => $cj_end_time, 
	"mails" => sizeof($list2), 
	"items" => sizeof($list1)
];

if(!empty($list1)) $logArray["removed"] = $list1;

$logger->info("CRON - Cronjob cleaning", $logArray);

