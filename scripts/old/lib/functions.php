<?php

// DB access
function getDBManager() {
	$db = $GLOBALS['settings']['db'];

	return new \MongoDB\Driver\Manager("mongodb://".$db['username'].":".$db['password']."@".$db['host']);
}

function getDocuments($collection, $filter, $options) {

	$mng = getDBManager();

	$query = new \MongoDB\Driver\Query($filter, $options);
	$doc = $mng->executeQuery($GLOBALS['settings']['db']['database'].".".$collection, $query);

	return $doc->toArray();
}

function deleteDocument($collection, $doc) {

	$mng = getDBManager();

	$bulk = new \MongoDB\Driver\BulkWrite;
	
	$bulk->delete($doc);

	$mng->executeBulkWrite($GLOBALS['settings']['db']['database'].".".$collection, $bulk);

}

// Disk access
function checkFolderName($name) {

	if($name == "") return false;

	if(!preg_match("/^MCD/", $name)) return false;

	if(preg_match("/\.|\/|\\\|\-/", $name)) return false;

	return true;	

}

function removeProject($collection, $id) {

	$projectData = reset(getDocuments($collection, ['_id' => $id], []));

	if(file_exists($GLOBALS['settings']['diskPath'].$projectData->folder) && checkFolderName($projectData->folder)) {
		exec("rm -rf ".$GLOBALS['settings']['diskPath'].$projectData->folder);
	}

	deleteDocument($collection, ['_id' => $projectData->_id]);	

}

// Warning email
function sendWarningEmail($projectData) {

	$mailer = new PHPMailer;

	try {	

		$mailer->CharSet = 'UTF-8';
		$mailer->IsSMTP();
		$mailer->SMTPDebug = 0;
		$mailer->Host = $GLOBALS['settings']['mail']['host'];
		$mailer->SMTPAuth = true;
		$mailer->SMTPSecure = 'ssl';
		$mailer->Port = $GLOBALS['settings']['mail']['port'];
		$mailer->Username = $GLOBALS['settings']['mail']['login'];
		$mailer->Password = $GLOBALS['settings']['mail']['password'];
		$mailer->isHTML(true);

		//Recipients
		$mailer->setFrom($GLOBALS['settings']['mail']['login'], $GLOBALS['settings']['fromName']);
		$mailer->addAddress($projectData->email, 'MCDNA User');
		

		//Content
		$mailer->isHTML(true); 
		$mailer->Subject = 'Your MCDNA project is about to expire!';

		$template = new Template();
		$template->assign('absoluteURL', $GLOBALS['settings']['absoluteURL']);
		$template->assign('projectID', $projectData->_id);
		$body = $template->parse($GLOBALS['settings']['serverPath'] . 'scripts/templates/mail-warning-user.html');

		if($body != "0") {
			$mailer->Body  = $body;
			$mailer->send();
		}

	} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: ".$mailer->ErrorInfo."\n";
	}

}

function warnUser($collection, $id) {

	$projectData = reset(getDocuments($collection, ['_id' => $id], []));
	
	if($projectData->email != "") sendWarningEmail($projectData);

}

// Interface functions
function printList($list) {

	foreach($list as $item) {

		var_dump("$item\n");

	}

}
