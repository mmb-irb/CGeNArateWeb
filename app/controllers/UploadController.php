<?php
namespace App\Controllers;

use App\Models\User;
use Respect\Validation\Validator as v;

class UploadController extends Controller {

	private function getPDBList() {

		//$aux = file($this->global['scriptsPath'].'list.MCDNA');
		$aux = file('/scripts/MCDNA/list.MCDNA');

		$arrayPDB = array();

		foreach($aux as $a) $arrayPDB[] = trim(preg_replace('/\s\s+/', ' ', $a));
		
		return $arrayPDB;

	}

	public function input($request, $response, $args) {

		if(isset($args['sampletype'])) {
			$sampleinput = true;
			$sampleSequence = $this->global['input']['samplesequence'][$args['sampletype']];
			$numStr = $this->global['input']['numstructures'][$args['sampletype']];
		} else {
			$sampleinput = false;
			$sampleSequence = $this->global['input']['samplesequence'][0];
			$sampleNP = $this->global['input']['samplenucleosomes'][0];
			$numStr = $this->global['input']['numstructures'][0];
		}

		$vars = [
			'page' => [
				'title' => 'Input Data - '.$this->global['longProjectName'],
				'description' => 'Upload all the data to create a new project',
				'basename' => 'input',
				'cookies' => $_COOKIE['cookie_consent'] === null ? true : false,
				'ga' => $_COOKIE['cookie_consent'] === 'accepted' ? true : false,
			],
			'tools' => $this->global['input']['tools'],
			'types' => $this->global['input']['types'],
			'resolution' => $this->global['input']['resolution'],
			'operations' => $this->global['input']['operations'],
			'PDBList' => $this->getPDBList(),
			'sampleSequence' => $sampleSequence,
			'sampleNP' => $sampleNP,
			'numStr' => $numStr,
			'sampleProtein' =>  $this->global['input']['sampleprot'],
			'sampleinput' => $sampleinput,
			'sampleType' =>  $args['sampletype']
		];  

		$this->view->render($response, 'input.html', $vars);

	}
	
	private function generateJSONVar($array, $type) {

		$c = 1;
		$out = '[';
		foreach($array as $a) {
			switch($type){
				case 'number': if($a == '?') $a = '"null"';
											 $out .= $a.',';
											 break;
				case 'string': if($a == '?') $a = '"null"';
											 $out .= '"'.$a.'",';
											 break;
			}
			$c ++;
		}
		$out = rtrim($out, ',');
		$out .= ']';

		return $out;

	}
		
	public function affinity($request, $response, $args) {

		//$inputs = $request->getParsedBody();
		$inputs = $request->getQueryParams();

		//$command = sprintf($this->global['affinity']['script'], $this->global['scriptsGlobals'], $inputs["seq"], $inputs["prot"], $inputs["pos"], $this->global['affinity']['param1'], $this->global['affinity']['param2'], $this->global['affinity']['param3']);
		$command = sprintf($this->global['affinity']['script'], $inputs["seq"], $inputs["prot"], $inputs["pos"], $this->global['affinity']['param1'], $this->global['affinity']['param2'], $this->global['affinity']['param3']);
		exec($command, $output);

		array_shift($output);

		$arrayXDataPlot = array();
		$arrayYDataPlot = array();
		foreach($output as $a) {
			$a = ltrim($a);
			$a = rtrim($a);
			$a = str_replace("\"", "", $a);
			$a = preg_split('/\,/', $a);
			$arrayXDataPlot[] = $a[1];
			$arrayYDataPlot[] = $a[2];
		}

		$lastx = end($arrayXDataPlot);
		$additionalx = range($lastx + 1, strlen($inputs["seq"]));
		$arrayXDataPlot = array_merge($arrayXDataPlot, $additionalx);

		$county = count($arrayYDataPlot);
		$additionaly = array_fill($county + 1, strlen($inputs["seq"]) - $county + 1, 'null');
		$arrayYDataPlot = array_merge($arrayYDataPlot, $additionaly);

		$px = $this->generateJSONVar($arrayXDataPlot, 'number');
		$py = $this->generateJSONVar($arrayYDataPlot, 'number');

		$json = '{';
		$json .= '"px":'.$px.',';
		$json .= '"py":'.$py;
		$json .= '}';

		echo $json;
	
	}

	public function upload($request, $response, $args) {

		$files = $request->getUploadedFiles();
  	$inputs = $request->getParsedBody();
		$diskPath = $this->global['diskPath'];

		// validate if correct data in form
		$validationArray = [
			//'seqfile' => v::exists(),
			//'email' => v::email(),
			'operations' => v::notEmpty(),
			'tool' => v::notEmpty(),
			//'type' => v::notEmpty(),

		];

		$validation = $this->validator->validate($request, $validationArray);

		if($validation->failed()) {

			$this->flash->addMessage('error', 'ERROR: You must fill all fields.');
			$this->logger->error("WEB - Upload data: form data incomplete", [$inputs]);
			echo '{ "status":0, "msg":"ERROR: You must fill all compulsory fields." }';
			return false;	
		}

		switch($inputs['typeupload']) {

			case 1:	if (empty($files['seqfile'])) {
						$this->logger->error("WEB - Upload data: file not provided");
						echo '{ "status":0, "msg":"You must provide a file" }';
						return false;
					}else{

						$s = file_get_contents($files['seqfile']->file);

						if(!preg_match('/^[CAGT]{15,}$/', $s)) {
							$this->logger->error("WEB - Upload data: Invalid DNA sequence or length is less than 15 characters", [$s]);
							echo '{ "status":0, "msg":"Invalid DNA sequence or length is less than 15 characters" }';
							return false;
						}

						if ($files['seqfile']->getError() === UPLOAD_ERR_OK) {

							/*$fout = fopen($files['seqfile']->file, "a+");
							fwrite($fout, PHP_EOL);
							fclose($fout);*/

							$this->saveFileAndData($files['seqfile'], $inputs, $diskPath, $files['seqfile']->getClientFilename(), null);
						}else{
							$this->logger->error("WEB - Upload data: file not uploaded");
							echo '{ "status":0, "msg":"Error handling the file" }';
							return false;
						}	
					}
					break;

			case 2:	$sequence = strtoupper($inputs['seqtxt']);
					
					// check sequence format and size
					if(!preg_match('/^[CAGT]{15,}$/', $sequence)) {
						$this->logger->error("WEB - Upload data: Invalid DNA sequence or length is less than 15 characters", [$sequence]);
						echo '{ "status":0, "msg":"Invalid DNA sequence or length is less than 15 characters" }';
						return false;
					}

					// check if nuclpos exists in case tool = Chormatin Dynamics
					if(empty($inputs['nuclpos']) && $inputs['tool'] == 4 ) {
						$this->logger->error("WEB - The nucleosomes positions are required.", [$inputs['nuclpos']]);
						echo '{ "status":0, "msg":"The nucleosomes positions are required." }';
						return false;
					}

					$tmpFile2 = null;

					if (!empty($inputs['nuclpos'])) {

						$nuclpos = $inputs['nuclpos'];

						// check nuclpos format
						if(!preg_match('/^\d+(?:\s\d+){0,}$/', $nuclpos)) {
							$this->logger->error("WEB - You must use the next format for nucleosomes positions: \'10 15 23 53\'.", [$nuclpos]);
							echo '{ "status":0, "msg":"You must use the next format for nucleosomes positions: \'10 15 23 53\'." }';
							return false;
						}

						// check if sequence and nulcpos match
						if(!$this->matchSeqNP($sequence, $nuclpos)) {
							$this->logger->error("WEB - Sequence and nucleosomes positions don\'t match.", [$nuclpos]);
							echo '{ "status":0, "msg":"Sequence and nucleosomes positions don\'t match, please check the help tooltip of nucleosome positions." }';
							return false;
						}

						// create tmp file with nuclpos
						if(!$tmpFile2 = $this->createFileFromSequence($nuclpos, $diskPath, "NP")) {
							$this->logger->error("WEB - Upload data: nucleosomes positions file not created");
							echo '{ "status":0, "msg":"Error creating new nucleosomes positions file" }';
							return false;
						}

					}

					// create tmp file with sequence
					if(!$tmpFile = $this->createFileFromSequence($sequence, $diskPath, "IS")) {
						$this->logger->error("WEB - Upload data: sequence file not created");
						echo '{ "status":0, "msg":"Error creating new sequence file" }';
						return false;
					}

					$this->saveFileAndData(null, $inputs, $diskPath, null, $tmpFile, $tmpFile2);

					break;

		}

	}

	// Nucleosome Positions and Sequence matching
	private function matchSeqNP($sequence, $nuclpos) {

		// get sequence length
		$seqlen = strlen($sequence);

		// sort nuleosomes positions
		$np = explode(" ", $nuclpos);
		sort($np);

		// before the first and after the last nucleosome there have to be at least 5 base pairs
		if($np[0] < 5 or end($np) > ($seqlen - 5)) return false;

		// Between two nucleosomes there has to be at least 3 base pairs.
		$last = 0;
		foreach ($np as $v) {
			if(($v - $last) < 3) return false;

			$last = $v;
		}

		return true;

	}

	private function createFileFromSequence($sequence, $path, $prefix) {

		$fileName = uniqid($prefix, true).".txt";

		$filePath = $path.'.tmp/'.$fileName;

		$fileSize = file_put_contents($filePath, strtoupper($sequence));

		$fout = fopen($filePath, "a+");
		fwrite($fout, PHP_EOL);
		fclose($fout);
		
		if($fileSize == 0) {
			unlink($filePath);
			return false;
		} 
			
		return $filePath;
		
	}

	private function saveFileAndData($file, $inputs, $path, $fileName, $tmpFile, $tmpFile2 = null) {

		switch($inputs['typeupload']) {

			case 1:	$fileName = preg_replace('/[^A-Za-z0-9_.-]/', "", $fileName);

					if($file->getSize() == 0) {
						echo '{ "status":0, "msg":"The file provided is empty" }';
						return false;
					}
					break;

			case 2: $fileName = "inputSequence.txt";
					if(isset($tmpFile2)) $fileName2 = "nucleosomePositions.txt";
					break;

		}

		$randomid = uniqid();
		$folderID = $this->global['shortProjectName'].$randomid;

		if(!$this->createFolder($path, $folderID)) {

			echo '{ "status":0, "msg":"Error creating folder" }';
			return false;

		}

		$uid = $this->createProject($inputs, $randomid, $fileName, $fileName2);
		
		switch($inputs['typeupload']) {

			case 1: $file->moveTo("$path$folderID/$fileName");
							break;

			case 2: rename($tmpFile, "$path$folderID/$fileName");
					if(isset($tmpFile2)) rename($tmpFile2, "$path$folderID/$fileName2");
							break;

		}

		if($inputs['tool'] == 3) {

			$workdir = $this->global['diskPath'].$folderID;
			$out = "$workdir/proteins.mcdna.in";

			$fout = fopen($out, "w");

			foreach($inputs["protein"] as $p) fwrite($fout, $p["length"]." ".$p["code"]." ".$p["position"]."\n");

			fclose($fout);

		}

		if(isset($inputs["numStruct"])) $numStructures = $inputs["numStruct"];
		else $numStructures = 1;

		if(isset($inputs["deltaLN"])) $deltaLN = $inputs["deltaLN"];
		else $deltaLN = "-1";

		if(isset($inputs["iterStruct"])) $iterStruct = $inputs["iterStruct"];
		else $iterStruct = "25000000";

		if(isset($inputs["analysis"])) $analysis = intval($inputs["analysis"]);
		else $analysis = 0;

		$this->launchJob($folderID, $fileName, $fileName2, $uid, $inputs["operations"], $inputs["tool"], $inputs["resolution"], $numStructures, $analysis, $deltaLN, $iterStruct);

		//echo '{ "status":1, "uid":"'.$uid.'" }';

	}

	private function createFolder($path, $folderID) {
	
		$d = mkdir($path.$folderID);
		chown($path.$folderID, 'www-data');
		chgrp($path.$folderID, 'www-data');
		return $d;

	}

	private function createProject($inputs, $randomid, $fileName, $fileName2 = null) {
		
		$data["_id"] = uniqid('', true);
		$data["id"] = $this->global['shortProjectName'].'USER'.$randomid;
		if(isset($inputs["email"])) $data["email"] = $inputs["email"];
		$data["tool"] = $inputs["tool"];
		//$data["toolType"] = $inputs["type"];
		$data["resolution"] = $inputs["resolution"];
		$data["operations"] = $inputs["operations"];
		if(isset($inputs["numStruct"])) $data["numStructures"] = $inputs["numStruct"];
		else  $data["numStructures"] = 1;
		if(isset($inputs["deltaLN"])) $data["deltaLN"] = $inputs["deltaLN"];
		if(isset($inputs["iterStruct"])) $data["iterStruct"] = $inputs["iterStruct"];
		if(isset($inputs["protein"])) $data["proteins"] = $inputs["protein"];
		if(isset($inputs["analysis"])) $data["analysis"] = intval($inputs["analysis"]);
		else $data["analysis"] = 0;
		$data["folder"] = $this->global['shortProjectName'].$randomid;
		$data["inputFile"] = $data["folder"]."/".$fileName;
		if(isset($fileName2)) $data["inputFileNP"] = $data["folder"]."/".$fileName2;
		$data["type"] = 1000;	
		$data["status"] = 1;	
		$data["uploadDate"] = new \MongoDB\BSON\UTCDateTime();

		//$this->db->insertDocument("projects", $data);
		$this->projects->createNewProject($data);

		return $data["_id"];
		
	}

	private function launchJob($folderID, $fileName, $fileName2, $uid, $operations, $tool, $resolution, $numStructures, $analysis, $deltaLN = "-1", $iterStruct = "25000000") {

		$workdir = $this->global['diskPath'].$folderID;
		$workdirWF = $this->global['wfDataPath'].$folderID;
		$out = "$workdir/launch.sh";

		$fout = fopen($out, "w");
		fwrite($fout, "#!/bin/csh\n");
		/*fwrite($fout, "#\$ -q $queuename\n");
		fwrite($fout, "#\$ -cwd\n");
		fwrite($fout, "#\$ -N $folderID\n");
		fwrite($fout, "#\$ -e $workdirWF/error.log\n");
		fwrite($fout, "#\$ -o $workdirWF/output.log\n\n");*/
		//fwrite($fout, "#\$ -M genis.bayarri@irbbarcelona.org\n");
		//fwrite($fout, "#\$ -m bea\n");
		//fwrite($fout, "source ".$this->global['scriptsGlobals']."MCDNA_Scripts_globalVars.sh\n\n");*/
		fwrite($fout, "cd $workdirWF\n\n");

		//fwrite($fout, "hostname > hostname.out\n\n");

		fwrite($fout, "echo \"## CGeNArate ##\"\n");

		/*if(in_array("createStructure", $operations) && (sizeof($operations) == 1)) $op = 0;
		elseif (in_array("createTrajectory", $operations) && (sizeof($operations) == 1)) $op = 1;
		elseif (sizeof($operations) == 2) $op = 2;*/

		$op = 1;

		// RESOLUTION AND DELTA LINKING NUMBER!!!!

		if($tool == 1) fprintf($fout, $this->global['sh']['plbase'], $this->global['scriptsPath'], $workdirWF, $fileName, $numStructures, $resolution, $op);
		else if($tool == 2) fprintf($fout, $this->global['sh']['plcirc'], $this->global['scriptsPath'], $workdirWF, $fileName, $numStructures, $deltaLN, $iterStruct, $resolution, $op);
		else if($tool == 3) fprintf($fout, $this->global['sh']['plprot'], $this->global['scriptsPath'], $workdirWF, $fileName, $numStructures, 'proteins.mcdna.in', $resolution, $op);
		else if($tool == 4) fprintf($fout, $this->global['sh']['plchrdyn'], $this->global['scriptsPath'], $workdirWF, $fileName, $workdir, $fileName2, $numStructures, $op);

		// MIRAR AMB ADAM!!!!
		/*switch($tool) {

			case 1: $toolaux = "mcdna";
							break;
			case 2: $toolaux = "circular";
							break;
			case 3: $toolaux = "prot";
							break;

		}*/
		// MIRAR AMB ADAM!!!!

		/*
		//perl runMCDNA_all.pl <seq_file> <num_structures> <mode> <all-atom> [0 (or none): <eqStruct>, 1: <create_traj>, 2: <create_both_eqStruct_and_traj>]
		//Example: perl runMCDNA_all.pl seq_56merSPCE.dat 10 3 1 0
		//
		//Usage: perl runMCDNA_circular_all.pl <seq_file> <num_structures> <segment_length> <linking_number> <number_iterations> <all-atom> [0 (or None): <eqStruct>, 1: <create_traj>, 2: <create_both_eqStruct_and_traj>]
		//Example: perl runMCDNA_circular_all.pl seq_56merSPCE.dat 10 5 -1 5000 1 0  # To generate a single structure (AA) (equilibrated)
		//
		//Usage: perl runMCDNA_Prots_all.pl <seq_file> <num_structures> <mode> <mcdna_prot config file> <all-atom> [0 (or None): eqStruct, 1: <create_traj>, 2: <create_both_eqStruct_and_traj>]
		//Example: perl runMCDNA_Prots_all.pl seq_56merSPCE.dat 10 3 mcdna_prot.config 1 0 # To generate a single structure (AA) (equilibrated)

		'plbase' => 'perl %srunMCDNA_all.pl %s/%s %s 3 %s %s'."\n\n",
		'plcirc' => 'perl %srunMCDNA_circular_all.pl %s/%s %s 5 %s 5000 %s %s'."\n\n",
		'plprot' => 'perl %srunMCDNA_Prots_all.pl %s/%s %s 3 %s %s %s'."\n\n",
		*/

		/*if(in_array("createTrajectory", $operations)) {

			if($tool == 1) fprintf($fout, $this->global['sh']['pltrajcg'], $this->global['scriptsPath'], $workdir, $fileName, $numStructures);
			else if($tool == 2) fprintf($fout, $this->global['sh']['pltrajcirc'], $this->global['scriptsPath'], $workdir, $fileName, $numStructures);
			else if($tool == 3) fprintf($fout, $this->global['sh']['pltrajprot'], $this->global['scriptsPath'], $workdir, $fileName, $numStructures, 'proteins.mcdna.in');
		
		} else {

			if($tool == 1) fprintf($fout, $this->global['sh']['plstrcg'], $this->global['scriptsPath'], $workdir, $fileName);
			else if($tool == 2) fprintf($fout, $this->global['sh']['plstrcirc'], $this->global['scriptsPath'], $workdir, $fileName);
			else if($tool == 3) fprintf($fout, $this->global['sh']['plstrprot'], $this->global['scriptsPath'], $workdir, $fileName, 'proteins.mcdna.in');
	
		}*/

		if($analysis == 1) {
			fwrite($fout, "echo \"## Analysis ##\"\n");
			//fprintf($fout, $this->global['sh']['rtraj'], $this->global['scriptsPath']);
			//fprintf($fout, $this->global['sh']['rstr'], $this->global['scriptsPath']);
			fprintf($fout, $this->global['sh']['analysis'], $this->global['analysisPath'], $tool, $resolution, $op);
		}

		fwrite($fout, "echo \"## Execute end-of-work routines ##\"\n");
		fprintf($fout, $this->global['sh']['end'], $this->global['absoluteURLMail'], $uid);

		fclose($fout);

		$outdck = "$workdir/launchDocker.sh";
		$queuename = $this->global['sh']['queuename'];

		$cpus = $this->global['sge']['cpus'];
		$mem = $this->global['sge']['mem'];

		$foutdck = fopen($outdck, "w");
		fwrite($foutdck, "#!/bin/csh\n");
		fwrite($foutdck, "#\$ -q $queuename\n");
		fwrite($foutdck, "#\$ -cwd\n");
		fwrite($foutdck, "#\$ -N $folderID\n");
		fwrite($foutdck, "#\$ -e $workdir/error.log\n");
		fwrite($foutdck, "#\$ -o $workdir/output.log\n\n");
		fwrite($foutdck, "hostname > hostname.out\n\n");
		fwrite($foutdck, "docker run --rm -v workflow_data:/mnt -v workflow_scripts:/app/Scripts --cpus \"$cpus\" --memory \"$mem\" workflow_image sh $workdirWF/launch.sh\n");

		fclose($foutdck);

		if(!file_exists($out) || !file_exists($outdck)) {

			$this->logger->error("WEB - CSH creation: file(s) not created", [$out, $outdck, $workdir]);

			echo '{ "status":0, "uid":"'.$uid.'" }';

		}else{

			$pid = $this->sge->start($workdir, $outdck);

			if($pid == 0) {
				$this->logger->error("WEB - SGE: job not submitted", [$outdck, $workdir]);
				echo '{ "status":0, "msg":"ERROR: job not submitted." }';
				return false;
			}

			$this->projects->updatePID($uid, $pid);

			//var_dump($pid, $uid);

			echo '{ "status":1, "uid":"'.$uid.'" }';

		}

	}

	/*public function checkSGE($request, $response, $args) {

		$this->createFolder($this->global['diskPath'], 'test');

		$out = $this->global['diskPath']."test/launch.sh";

		$fout = fopen($out, "w");
		fwrite($fout, "#!/bin/csh\n");
		fwrite($fout, "#\$ -q local.q\n");
		fwrite($fout, "#\$ -N test\n");
		//fwrite($fout, "docker run --rm -v workflow_data:/mnt -e ARG_VALUE=test workflow_image");
		fwrite($fout, "docker run --rm -v workflow_data:/mnt -v workflow_scripts:/app/Scripts workflow_image sh /app/Scripts/launch_lin.sh");
		fclose($fout);


		exec("ssh -o StrictHostKeyChecking=no application@my_stack_sge \"qsub /data/Web/test/launch.sh\"", $op);
		var_dump($op);

		//echo '{ "status":1 }';

	}*/

}
