<?php
namespace App\Controllers;

use App\Models\User;

class OutputController extends Controller {

	protected $varsNF = [
  	'page' => [
  	'title' => 'MC DNA - Page not found!',
  	'description' => 'The page you\'re requesting doesn\'t exist',
  	'basename' => 'error'
   	],
  ];

	private function getData($id) {

		return $this->projects->getProjectData($id);
	
	}

	public function getPath($request, $response, $args) {

		$project = $this->getData($args['id']);

		if($project->resolution == 1) $resolution = "AA";
		else  $resolution = "CG";

		$dpath = $this->global['filesPath'].$project->folder;

		$output = '{';
		$output .= '"path":"'.$this->global['baseURL'].$this->global['filesPathName'].'/'.$project->folder.'/",';
		if(in_array("createStructure", $project->operations)) {
			/*$output .= '"strPath":"EQ_'.$resolution.'/",';
			if(file_exists($dpath.'/EQ_'.$resolution.'/'.$this->global['summary']['strPDB'])) $output .= '"strPDB":"'.$this->global['summary']['strPDB'].'",';
			else {
				$this->logger->error("WEB - File not found", [$dpath.'/EQ_'.$resolution.'/'.$this->global['summary']['strPDB']]);
				$output .= '"strPDB":"error",';
			}*/
			$output .= '"strPath":"TRAJ_'.$resolution.'/",';
			if(file_exists($dpath.'/TRAJ_'.$resolution.'/'.$this->global['summary']['trajPDB'])) $output .= '"strPDB":"'.$this->global['summary']['trajPDB'].'",';
			else  {
				$this->logger->error("WEB - File not found", [$dpath.'/TRAJ_'.$resolution.'/'.$this->global['summary']['trajPDB']]);
				$output .= '"strPDB":"error",';
			}
		}
		if(in_array("createTrajectory", $project->operations)) {
			$output .= '"trajPath":"TRAJ_'.$resolution.'/",';
			if(file_exists($dpath.'/TRAJ_'.$resolution.'/'.$this->global['summary']['trajPDB'])) $output .= '"trajPDB":"'.$this->global['summary']['trajPDB'].'",';
			else  {
				$this->logger->error("WEB - File not found", [$dpath.'/TRAJ_'.$resolution.'/'.$this->global['summary']['trajPDB']]);
				$output .= '"trajPDB":"error",';
			}
			if(file_exists($dpath.'/TRAJ_'.$resolution.'/'.$this->global['summary']['trajDCD'])) $output .= '"trajDCD":"'.$this->global['summary']['trajDCD'].'",';
			else { 
				$this->logger->error("WEB - File not found", [$dpath.'/TRAJ_'.$resolution.'/'.$this->global['summary']['trajDCD']]);
				$output .= '"trajDCD":"error",';
			}

			$output .= '"PCAZipPath":"'.$this->global['baseURL'].$this->global['filesPathName'].'/'.$project->folder.sprintf($this->global['flex']['foldertraj'], $resolution).$this->global['NAFlex']['naflextraj'].$this->global['pcazip']['folder'].'"';

		}
		$output = rtrim($output, ",");
		$output .= '}';

		//echo $this->global['baseURL'].$this->global['filesPathName'].'/'.$project->folder.'/';
		echo $output;
	
	}

	// SUMMARY
	
	private function getFlexTypes($folder) {

		$path = $this->global['filesPathName'].'/'.$folder;

		chdir($path);

		$str = implode(",", glob("EQ_*"));
		$traj = implode(",", glob("TRAJ_*"));

		$arrayTypes = array();

		if(file_exists($str."/".$this->global['analysisPathName'])) $arrayTypes[] = 1;
		if(file_exists($traj."/".$this->global['analysisPathName'])) $arrayTypes[] = 2;

		return $arrayTypes;

	}

	private function getFlexSubTypes($folder, $strtype, $tool) {

		// 1: Curves
		// 2: Stiffness
		// 3: PCA
		// 4: Contacts
		// 5: Bending
		// 6: Circular
		// 7: Energy penalty
		// 8: End-to-end distance
		// 9: SASA

		if($strtype == 'eq') {
			$p = implode(",", glob("EQ_*"));
			$sp = 'PDB/';
		} else {
			$p = implode(",", glob("TRAJ_*"));
			$sp = '';
		}

		$arraySubTypes = array();
		
		if(file_exists($p."/".$this->global['analysisPathName']."/NAFlex/".$sp.$this->global['curves']['folder'])) $arraySubTypes[] = 1;
		if(file_exists($p."/".$this->global['analysisPathName']."/NAFlex/".$sp.$this->global['stiffness']['folder'])) $arraySubTypes[] = 2;
		if(file_exists($p."/".$this->global['analysisPathName']."/NAFlex/".$sp.$this->global['pcazip']['folder'])
			&& file_exists($p."/".$this->global['analysisPathName']."/NAFlex/".$sp.$this->global['pcazip']['folder']."/NAFlex_pcazipOut.evals")
			&& file_exists($p."/".$this->global['analysisPathName']."/NAFlex/".$sp.$this->global['pcazip']['folder']."/NAFlex_pcazipOut.collectivity")
			&& file_exists($p."/".$this->global['analysisPathName']."/NAFlex/".$sp.$this->global['pcazip']['folder']."/NAFlex_pcazipOut.stiffness")
			&& $tool != 2
		) $arraySubTypes[] = 3;
		if(file_exists($p."/".$this->global['analysisPathName']."/NAFlex/".$sp.$this->global['contacts']['folder'.$strtype])) $arraySubTypes[] = 4;
		if(file_exists($p."/".$this->global['analysisPathName']."/".$this->global['bending']['folder']) && count(scandir($p."/".$this->global['analysisPathName']."/".$this->global['bending']['folder'])) > 2) $arraySubTypes[] = 5;
		if(file_exists($p."/".$this->global['analysisPathName']."/".$this->global['circular']['folder'])) $arraySubTypes[] = 6;
		if(file_exists($p."/".$this->global['analysisPathName']."/".$this->global['energy']['folder'])) $arraySubTypes[] = 7;
		if(file_exists($p."/".$this->global['analysisPathName']."/NAFlex/".$sp.$this->global['end-to-end']['folder'])) $arraySubTypes[] = 8;
		if(file_exists($p."/".$this->global['analysisPathName']."/".$this->global['sasa']['folder'])) $arraySubTypes[] = 9;

		asort($arraySubTypes);

		return $arraySubTypes;

	}

	private function getFirstFlexType($flextypes, $folder, $tool) {

		$firsttype = array();

		// EQUILIBRATED
		if (in_array(1, $flextypes)) {
			$fst = $this->getFlexSubTypes($folder, 'eq', $tool);
			$firsttype[0] = $fst[0];
		}

		// TRAJECTORY
		if (in_array(2, $flextypes)) {
			$fst = $this->getFlexSubTypes($folder, 'traj', $tool);
			$firsttype[1] = $fst[0];
		}

		return $firsttype;

	}

	private function pageExists($projectData, $request, $response) {

		if(!$projectData) {

			$this->logger->error("WEB - Page not found", [$request->getUri()->getPath()]);
			$this->view->render($response->withStatus(404), '404.html',$this->varsNF);

			return false;
		}

		return true;

	}	

	private function getWFID($projectData) {

		$stepsID = ''; 
		switch($projectData->tool) {
			case "1": $stepsID .= "MCDNA.";
								break;
			case "2": $stepsID .= "CIRC.";
								break;
			case "3": $stepsID .= "PROTDNA.";
								break; 
		}

		switch($projectData->resolution) {
			case "0": $stepsID .= "CG.";
								break;
			case "1": $stepsID .= "AA.";
								break;
		}

		if(sizeof($projectData->operations) == 2) {
			$stepsID .= "STTR.";
		} else {
			if($projectData->operations[0] == "createStructure") $stepsID .= "ST.";
			else $stepsID .= "TR.";
		}

		if($projectData->analysis == 1) $stepsID .= "AN";
		else $stepsID .= "NA";

		return $stepsID;

	}

	private function getArrayOfSteps($steps, $projectData) {

		$sections = explode("##", $steps);

		if($sections[0] == "") array_shift($sections);

		$output = [];
		$cs = 0;
		foreach($sections as $sec) {

			$stps = explode("#", $sec);

			$output[$cs]["tit"] = preg_replace('/[0-9]+/', $projectData->numStructures, ucfirst(strtolower(trim(array_shift($stps)))));

			$a = [];
			foreach($stps as $st) {
				$a[] = preg_replace('/ STEP [0-9]+: /', '', $st);
			}
			$output[$cs]["steps"] = $a;

			$cs ++;

		}

		return $output;

	}

	private function getWFSteps($projectData) {

		$stepsID = $this->getWFID($projectData);

		//$stepsID = "PROTDNA.AA.STTR.AN";

		$steps = reset($this->db->getDocuments("workflowSteps", ["_id" => $stepsID], []))->steps;

		return $this->getArrayOfSteps($steps, $projectData);
	
	}

	public function outputSummary($request, $response, $args) {

		$projectData = $this->getData($args['id']);
		
		if(!$this->pageExists($projectData, $request, $response)) return false;

		$inputSequence = file_get_contents($this->global['diskPath'].$projectData->inputFile);

		if(sizeof($projectData->operations) > 1) {

			$traj = 2;

		}else{

			if(in_array("createTrajectory", $projectData->operations)) $traj = 1;
			else $traj = 0;

		}

		$status = $this->sge->status($projectData->pid);


		$path = $this->global['filesPath']
						.$projectData->folder
						.$this->global['flex']['folder'];

		$flextypes = $this->getFlexTypes($projectData->folder);

		$firsttype = $this->getFirstFlexType($flextypes, $projectData->folder, $projectData->tool);

		if(isset($args['sample'])) $sample = true;

		
		// get WF steps
		$wfSteps = $this->getWFSteps($projectData);

		$vars = [
			'page' => [
				'title' => 'Output Summary - '.$this->global['longProjectName'],
				'description' => $this->global['longProjectName'] . ' output summary',
				'basename' => 'summary',
				'cookies' => $_COOKIE['cookie_consent'] === null ? true : false,
				'ga' => $_COOKIE['cookie_consent'] === 'accepted' ? true : false,
			],
			'id' => $args['id'],
			'projectData' => $projectData,
			'tool' => $this->global['input']['tools'][$projectData->tool],
			'type' => $this->global['input']['types'][$projectData->toolType],
			'operations' => $this->global['input']['operations'],
			'resolutions' => $this->global['input']['resolution'],
			'traj' => $traj,
			'inputSequence' => $inputSequence,
			'status' => $status,
			'defaultFlexEQ' => $this->global['flextypes'][$firsttype[0]],
			'defaultFlexTRAJ' => $this->global['flextypes'][$firsttype[1]],
			'flextypes' => $flextypes,
			'absoluteURL' => $this->global['absoluteURL'],
			'sample' => $sample,
			'wfSteps' => $wfSteps
		];  

		$this->view->render($response, 'outputsummary.html', $vars);

	}

	public function drawProgress($request, $response, $args) {

		$projectData = $this->getData($args['id']);

		$currentLog = trim(shell_exec("egrep '\# STEP|GENERATING|BUILDING|\# TRAJECTORY|\# STRUCTURE' ".$this->global['diskPath'].$projectData->folder."/output.log"));
		if($currentLog != "") $numCL = substr_count($currentLog, "\n") + 1;
		else $numCL = 0;

		$stepsID = $this->getWFID($projectData);
		$allSteps = ltrim(preg_replace('/[#]+/', "\n".'\0', reset($this->db->getDocuments("workflowSteps", ["_id" => $stepsID], []))->steps));

		$arrSteps = explode("\n", $allSteps);

		$remainingProgress = join("", array_slice($arrSteps, $numCL));

		$currentLog = preg_replace('/\n/', '', $currentLog);
		
		$prog = $this->getArrayOfSteps($currentLog, $projectData);
		$rem = $this->getArrayOfSteps($remainingProgress, $projectData);

		echo '<li class="progress-step step-title is-complete">
				<span class="progress-marker"><i class="fa fa-check-circle-o" aria-hidden="true"></i></span>
				<span class="progress-text">
					<h3 class="progress-title">Job submitted to queues</h3>
				</span>
			</li>';

		if($currentLog == "") {
			$class = ' is-active';
			$icon = '<i class="fa fa-spinner fa-spin fa-fw"></i>';
		} else {
			$class = ' is-complete';
			$icon = '<i class="fa fa-check" aria-hidden="true"></i>';
		}

		echo '<li class="progress-step '.$class.'">
						<span class="progress-marker">'.$icon.'</span>
						<span class="progress-text">
							<h4 class="progress-title">Waiting for a free processor</h4>
							'.$s.'
						</span>
					</li>';

		$cp = 1;
		foreach($prog as $p) {

			echo '<li class="progress-step step-title is-complete">
				<span class="progress-marker"><i class="fa fa-check-circle-o" aria-hidden="true"></i></span>
				<span class="progress-text">
					<h3 class="progress-title">'.$p['tit'].'</h3>
				</span>
			</li>';

			$c1 = 1;
			foreach($p['steps'] as $s) {

				if(($cp == (sizeof($prog))) && ($c1 == sizeof($p['steps']))) {
					$class = ' is-active';
					$icon = '<i class="fa fa-spinner fa-spin fa-fw"></i>';
				}
				else {
					$class = ' is-complete';
					$icon = '<i class="fa fa-check" aria-hidden="true"></i>';
				}
		
				echo '<li class="progress-step '.$class.'">
						<span class="progress-marker">'.$icon.'</span>
						<span class="progress-text">
							<h4 class="progress-title">Step '.$c1.'</h4>
							'.$s.'
						</span>
					</li>';
				$c1 ++;

			}

			$cp ++;

		}

		foreach($rem as $r) {

			if($r['tit'] != "") {
				echo '<li class="progress-step step-title">
					<span class="progress-marker"></span>
					<span class="progress-text">
						<h3 class="progress-title">'.$r['tit'].'</h3>
					</span>
				</li>';
				$c2 = 1;
			} else {
				$c2 = $c1;
			}

			foreach($r['steps'] as $s) {
		
				echo '<li class="progress-step">
						<span class="progress-marker"></span>
						<span class="progress-text">
							<h4 class="progress-title">Step '.$c2.'</h4>
							'.$s.'
						</span>
					</li>';
				$c2 ++;

			}

		}
		
	}

	// UTILITIES

	private function generateJSONVarWithCutoff($array, $cutoff, $cutoffvalue) {

		$c = 1;
		$out = '[';
		foreach($array as $a) {
			if($c == $cutoff) $out .= $cutoffvalue.',';
			else  $out .= '"'.$a.'",';
			$c ++;
		}
		$out = rtrim($out, ',');
		$out .= ']';

		return $out;

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

	// SPECIFIC FUNCTIONS

	private function getContactsJSON($path, $subsection, $type) {

		switch($type) {
			case 'NUC-NUC': 
				$typ = 'NUC-NUC';
				$output = $this->getContactsHeatmapJSON($path, $subsection, $typ);
				break;

			case (preg_match('/^NUC-[A-Z]{1}$/', $type) ? true : false) :
				$typ = 'PROT-NUC';
				$e = explode('-', $type);
				$output = $this->getContactsHeatmapJSON($path, $subsection, $typ, $e);
				break;

			case (preg_match('/^[A-Z]{1}-[A-Z]{1}$/', $type) ? true : false) :
				$typ = 'PROT-PROT';
				$e = explode('-', $type);
				$output = $this->getContactsHeatmapJSON($path, $subsection, $typ, $e);
				break;
				
			default: var_dump('others!');
		}

		return $output;

	}

	private function matchArrayWithString($array, $string) {
    $parts = explode("-", $string);
    if (count(array_intersect($array, $parts)) == 2) {
				return $parts;
		}
		return false;
	}

	private function getContactsHeatmapJSON($longpath, $subsection, $type, $prots = null) {

		$path = $longpath.$type.'/';

		switch($type) {

			case 'NUC-NUC':
				$fileName = "distanceMean.contactMap".strtoupper($subsection).".dat";
				// changing path to read the labels
				chdir($longpath);
				$arrayAxis = file("seqR.info");
				// changing path to read the .dat files
				chdir($path);
				$axisx = explode(" ", rtrim($arrayAxis[0]));
				$axisy = explode(" ", rtrim($arrayAxis[0]));
				break;

			case 'PROT-NUC':
				$fileName = "nuc-".$prots[1].".distanceMean.contactMap".strtoupper($subsection).".dat";
				// changing path to read the labels
				chdir($longpath);
				$arrayAxisX = file("seqR.".$prots[1].".info");
				$arrayAxisY = file("seqR.info");
				// changing path to read the .dat files
				chdir($path);
				$axisx = explode(" ", rtrim($arrayAxisX[0]));
				$axisy = explode(" ", rtrim($arrayAxisY[0]));
				$axis = null;
				break;

			case 'PROT-PROT':
				chdir($path);
		
				$fs = glob("prot*.dat");

				$protsName = array();

				foreach($fs as $f) {

					$a = explode(".", substr($f, 4, 3));

					$r = $this->matchArrayWithString($prots, $a[0]);
					if($r) {
						$protsName = $r;
						break;
					}

				}

				// changing path to read the labels
				chdir($longpath);
				$arrayAxisX = file("seqR.".$protsName[1].".info");
				$arrayAxisY = file("seqR.".$protsName[0].".info");
				// changing path to read the .dat files
				chdir($path);

				$fileName = "prot".$protsName[0]."-".$protsName[1].".distanceMean.contactMap".strtoupper($subsection).".dat";
				//var_dump($fileName);
				$axisx = explode(" ", rtrim($arrayAxisX[0]));
				$axisy = explode(" ", rtrim($arrayAxisY[0]));
				break;

			default:
				$fileName = null;

		}
		
		if(file_exists($path.$fileName)) {

			$isError = false;
		
			$ax = '[';
			foreach($axisx as $a) {
				//$ax .= '"'.substr($a, -1).'",';
				$ax .= '"'.$a.'",';
			}
			$ax = rtrim($ax, ',');
			$ax .= ']';

			$ay = '[';
			foreach($axisy as $a) {
				//$ax .= '"'.substr($a, -1).'",';
				$ay .= '"'.$a.'",';
			}
			$ay = rtrim($ay, ',');
			$ay .= ']';

			$arrayRows = file($fileName);

			$arrayData = array();
			foreach($arrayRows as $a) {
				$a = ltrim($a);
				$a = rtrim($a);
				$arrayData[] = preg_split('/\s+/', $a);
			}
			$json = '{';
			$json .= '"x":'.$ax.',';
			$json .= '"y":'.$ay.',';
			$json .= '"z":';
			$json .= '[';

			foreach($arrayData as $a1) {
				$json .= '[';
				
				foreach($a1 as $a2) {

					if($a2 == "NA") $isError = true;

					/*if($a2 != 0) $a2 = log($a2, 10);
					else $a2 = 4.6;*/
					$json .= $a2.',';
				}

				$json = rtrim($json, ',');
				$json .= '],';
			}

			$json = rtrim($json, ',');
			$json .= ']';

			if($type == 'PROT-PROT') $json .= ', "protOrder": ["'.$protsName[0].'","'.$protsName[1].'"]';

			$json .= '}';

			if($isError) {
				$this->logger->error("WEB - NA in File", [$path.$fileName]);
				return '{"error": true}';
			} else return($json);

		}else{

			$this->logger->error("WEB - File not found", [$path.$fileName]);
			return '{"error": true}';

		}

	}

	private function getStiffnessJSON($path, $subsection, $type) {

		switch($subsection) {
			case 'helical': 
				switch($type){
				case 'rise_avg':
				case 'roll_avg':
				case 'shift_avg':
				case 'slide_avg':
				case 'tilt_avg':
				case 'twist_avg':
						$output = $this->getStiffnessScatterPlotJSON($path, $subsection, $type, 1);
						break;
				}
			break;
			default: var_dump('others!');
		}

		return $output;

	}

	private function getCircularJSON($path, $subsection, $type, $strtype) {

		if($strtype == 'traj') {

			switch($subsection) {
				case 'circular': 
					switch($type){
					case 'rg':
					case 'twist':
					case 'writhe':
							$output = $this->getCircularScatterPlotJSON($path, $subsection, $type, 1);
							break;
					}
				break;
				default: var_dump('others!');
			}

		}else{

			/*switch($subsection) {
				case 'circular': 
					$output = $this->getCircularTable($path);
				break;
				default: var_dump('others!');
		}*/

		}

		return $output;

	}

	private function getBendingJSON($path, $subsection, $type, $strtype) {

		if($strtype == 'eq') {

			switch($subsection) {
				case 'bending': 
					switch($type){
					case 'distribution':
						$output = $this->getBendingScatterPlotJSON($path, ["Bending_distribution_total_bending_ensemble", "Bending_distribution_xz_yz_ensemble"]);
							break;
					
					case 'individual':
						$output = $this->getBendingScatterPlotJSON($path, ["Individual_bending_xz_yz_ensemble1", "Individual_bending_xz_yz_ensemble2"]);
							break;
					}
				break;
				default: var_dump('others!');
			}

		}else{

			switch($subsection) {
				case 'bending': 
					switch($type){
					case 'distribution':
						$output = $this->getBendingScatterPlotJSON($path, ["Bending_distribution_total_bending_ensemble", "Bending_distribution_xz_yz_ensemble", "Bending_distribution_total_whole_fiber_ensemble"]);
							break;
					
					case 'individual':
						$output = $this->getBendingScatterPlotJSON($path, ["Individual_bending_xz_yz_ensemble1", "Individual_bending_xz_yz_ensemble2"]);
							break;

					case 'fiber':
						$output = $this->getBendingScatterPlotJSON($path, ["Bending_distribution_perc_total_whole_fiber_ensemble"]);
							break;

					case 'fiberalong':
						$output = $this->getBendingScatterPlotJSON($path, ["Bending_total_perc_whole_fiber_alongtraj_ensemble", "Bending_total_whole_fiber_alongtraj_ensemble"]);
							break;

					}
				break;
				default: var_dump('others!');
			}

		}

		return $output;

	}

	private function getEnergyJSON($path, $subsection, $type, $strtype, $proteins = null) {

		switch($subsection) {
				case 'energy': 
					switch($type){
						case 'elen':
							$output = $this->getBendingScatterPlotJSON($path, ["total_elastic_energy", "total_deformation_energy"]);
								break;
						case 'unbound':
							$output = $this->getBendingScatterPlotJSON($path, ["total_elastic_energy_unbound"]);
								break;
						case 'dnaprot':
							$output = $this->getDNAProtPlotJSON($path, ["elastic_energy_dnaprot"], $proteins);
								break;
					}	
				break;
				default: var_dump('others!');
			}

		return $output;

	}

	private function getSasaJSON($path, $subsection, $type, $strtyp, $proteins = null, $proteinsList = null) {

		//var_dump($path, $subsection, $type, $strtype);

		switch($subsection) {
				case 'sasa': 
					$output = $this->getSasaScatterPlotJSON($path, $type, $proteins, $proteinsList);	
				break;
				default: var_dump('others!');
			}

		return $output;

	}

	private function getDNAProtPlotJSON($path, $files, $proteins) {

		if(file_exists($path.$files[0].'1.csv')) {

			chdir($path);

			$filesList = glob($files[0]."*.csv");

			$arrayPop = array();

			foreach($filesList as $fn) {

				$arrayAux = file($fn);
				array_shift($arrayAux);

				$arrayPop[] = $arrayAux;

			}

			$arrayValuesX = array();
			$arrayValuesY = array();
			$c = 0;
			foreach($arrayPop as $ap) {

				foreach($ap as $a) {

					$a = rtrim($a);
					$v = preg_split('/\,/', $a);
					$v[0] = str_replace('"', '', $v[0]);

					$arrayValuesX[$c][] = $v[0];
					$arrayValuesY[$c][] = $v[1];

				}
		
				$c ++;		

			}

			$json = '{';
			$json .= '"xaxis":{';
			$c = 1;
			foreach($arrayValuesX as $avx) {
				$json .= '"x'.$c.'":'.$this->generateJSONVar($avx, 'number').',';
				$c ++;
			}
			$json = rtrim($json, ",");
			$json .= '},';
			$json .= '"yaxis":{';
			$c = 1;
			foreach($arrayValuesY as $avy) {
				$json .= '"y'.$c.'":'.$this->generateJSONVar($avy, 'number').',';
				$c ++;
			}
			$json = rtrim($json, ",");
			$json .= '},';
			$json .= '"proteins":{';
			$c = 1;
			foreach($proteins as $p) {
				$json .= '"p'.$c.'":"'.$p->code.'",';
				$c ++;
			}
			$json = rtrim($json, ",");
			$json .= '}';
			$json .= '}';

			return($json);

		} else {

			$this->logger->error("WEB - File not found", [$path.$files[0].'1.csv']);
			return '{"error": true}';

		}

	}

	private function getSasaScatterPlotJSON($path, $snapshot, $proteins, $proteinsList) {

		$fileName = "sasa.".$snapshot.".dat";

		if(file_exists($path.'/'.$fileName)) {

			chdir($path);
			$arrayAux = file($fileName);

			$arrayValuesX = array();
			$arrayValuesY1 = array();
			$arrayValuesY2 = array();
			$arrayValuesY3 = array();
			foreach($arrayAux as $a) {
				$a = ltrim($a);
				$a = rtrim($a);
				$a = preg_split('/\s+/', $a);
				
				$arrayValuesX[] = $a[0];
				$auxval = $a[1];
				if($a[1] == NULL) $auxval = "?";
				$arrayValuesY1[] = $auxval;
				$auxval = $a[2];
				if($a[2] == NULL) $auxval = "?";
				$arrayValuesY2[] = $auxval;
				$auxval = $a[3];
				if($a[3] == NULL) $auxval = "?";
				$arrayValuesY3[] = $auxval;
			}

			// updating proteins for double strand
			$seqlen = sizeof($arrayValuesX);
			$protsAux = array();

			foreach($proteins as $p) {
				$np = new \stdClass;
				$np->code = $p->code;
				$np->position = $seqlen - $p->position - $p->length;
				$np->length = $p->length;

				$protsAux[] = $np;
			}

			$proteins = array_merge($proteins, $protsAux);

			$pList = array();
			foreach($proteinsList as $p) {
				$p = ltrim($p);
				$p = rtrim($p);
				$p = preg_split('/:/', $p);
				$pList[$p[0]] = $p[1];
			}

			$json = '{';
			$json .= '"x":'.$this->generateJSONVar($arrayValuesX, 'string').',';
			$json .= '"y1":'.$this->generateJSONVar($arrayValuesY1, 'number').',';
			$json .= '"y2":'.$this->generateJSONVar($arrayValuesY2, 'number').',';
			$json .= '"y3":'.$this->generateJSONVar($arrayValuesY3, 'number').',';
			$json .= '"proteins": [';
			foreach($proteins as $p) {
				$json .= '{';
				$json .= '"id":"'.$p->code.'",';
				$json .= '"pos1":'.intval($p->position).',';
				$json .= '"pos2":'.intval($p->position + $p->length).',';	
				$json .= '"chain":"'.$pList[$p->code].'"';
				$json .= '},';	
			}
			$json = rtrim($json, ",");
			$json .= ']';
			$json .= '}';

			return($json);

		} else {

			$this->logger->error("WEB - File not found", [$path.'/'.$fileName]);
			return '{"error": true}';

		}

	}

	private function getBendingScatterPlotJSON($path, $files) {
		
		$fileName = $files[0].".csv";

		if(file_exists($path.'/'.$fileName)) {

			chdir($path);

			$arrayPop = array();

			foreach($files as $f) {

				$fn = $f.".csv";

				$arrayAux = file($fn);
				array_shift($arrayAux);

				$arrayPop = array_merge($arrayPop, $arrayAux);
			}

			$arrayValuesX = array();
			$arrayValuesY = array();
			foreach($arrayPop as $a) {
				$a = rtrim($a);
				$v = preg_split('/\,/', $a);
				$first = array_shift($v);
				if(strlen($first) == 3) {
					$first = str_replace('"', '', $first);
				}else{
					if(strlen($first) == 2) $first = 1;
					else $first = 0; 
				}
				if((intval($first) % 2) == 1) {
					$arrayValuesX[] = $v;
				}else{
					$arrayValuesY[] = $v;
				}

			}

			/*var_dump("X AXIS: ");
			var_dump($arrayValuesX);
			var_dump("Y AXIS: ");
			var_dump($arrayValuesY);*/

			$json = '{';
			$c = 1;
			foreach($arrayValuesX as $avx) {
				$json .= '"x'.$c.'":'.$this->generateJSONVar($avx, 'number').',';
				$c ++;
			}
			$c = 1;
			foreach($arrayValuesY as $avy) {
				$json .= '"y'.$c.'":'.$this->generateJSONVar($avy, 'number').',';
				$c ++;
			}
			$json = rtrim($json, ",");
			$json .= '}';

			return($json);

		} else {

			$this->logger->error("WEB - File not found", [$path.'/'.$fileName]);
			return '{"error": true}';

		}

	}

	private function getCurvesJSON($path, $subsection, $type) {

		switch($subsection) {
			case 'backbone_torsions': 
				switch($type){
					case 'BI_population':
					case 'canonical_alpha_gamma':
						$output = $this->getCurvesBarsPlotJSON($path, $subsection, $type, 2);
						break;
					case 'puckering': 
						$output = $this->getCurvesBarsPlotJSON($path, $subsection, $type, 4);
						break;
				}
			break;
			case 'axis_bp': 
				switch($type){
				case 'inclin_avg':
				case 'tip_avg':
						$output = $this->getCurvesScatterPlotJSON($path, $subsection, $type, 2);
						break;
				case 'xdisp_avg':
				case 'ydisp_avg':
						$output = $this->getCurvesScatterPlotJSON($path, $subsection, $type, 1);
						break;
				}
			break;
			case 'helical_bp': 
				switch($type){
				case 'shear_avg':
				case 'stretch_avg':
				case 'stagger_avg':
				case 'buckle_avg':
				case 'propel_avg':
				case 'opening_avg':
						$output = $this->getCurvesScatterPlotJSON($path, $subsection, $type, 2);
						break;
				}
			break;
			case 'helical_bpstep': 
				switch($type){
				case 'rise_avg':
				case 'roll_avg':
				case 'shift_avg':
				case 'slide_avg':
				case 'tilt_avg':
				case 'twist_avg':
						$output = $this->getCurvesScatterPlotJSON($path, $subsection, $type, 3);
						break;
				}
			break;
			case 'grooves': 
				switch($type){
				case 'majd_avg':
				case 'majw_avg':
				case 'mind_avg':
				case 'minw_avg':
						$output = $this->getCurvesScatterPlotJSON($path, $subsection, $type, 1);
						break;
				}
			break;
			default: var_dump('others!');
		}

		return $output;

	}

	private function getPCAZipJSON($path, $section, $subsection) {

		$fileName = "NAFlex_pcazipOut.proj".$subsection.".plot.dat";

		if(file_exists($path.$fileName)) {

			chdir($path);
			
			$arrayRows = file($fileName);
			$arrayXDataPlot = array();
			$arrayYDataPlot = array();
			foreach($arrayRows as $a) {
				$a = ltrim($a);
				$a = rtrim($a);
				$a = preg_split('/\s+/', $a);
				$arrayXDataPlot[] = $a[0];
				$arrayYDataPlot[] = $a[1];
			}

			$px = $this->generateJSONVar($arrayXDataPlot, 'number');
			$py = $this->generateJSONVar($arrayYDataPlot, 'number');
			
			$arrayRows = file("NAFlex_pcazipOut.proj".$subsection.".plot.dat.population.csv");
			$arrayXDataHist = array();
			$arrayYDataHist = array();
			foreach($arrayRows as $a) {
				$a = ltrim($a);
				$a = rtrim($a);
				$a = preg_split('/\,/', $a);
				$arrayYDataHist[] = $a[1];
				$arrayXDataHist[] = $a[2];
			}

			$hx = $this->generateJSONVar($arrayXDataHist, 'number');
			$hy = $this->generateJSONVar($arrayYDataHist, 'number');

			$json = '{';
			$json .= '"px":'.$px.',';
			$json .= '"py":'.$py.',';
			/*****************************/
			$json .= '"hx":'.$hx.',';
			$json .= '"hy":'.$hy;
			/*****************************/
			$json .= '}';

			return($json);

		}else{

			$this->logger->error("WEB - File not found", [$path.$fileName]);
			return '{"error": true}';

		}

	}

	private function getContactsDistJSON($path, $chains) {

		$fileName = $chains.".dat";

		if(file_exists($path.$fileName)) {

			chdir($path);
			
			$arrayRows = file($fileName);
			$fl = array_shift($arrayRows);
			$arrayXDataPlot = array();
			$arrayYDataPlot = array();
			foreach($arrayRows as $a) {
				$a = ltrim($a);
				$a = rtrim($a);
				$a = preg_split('/\s+/', $a);
				$arrayXDataPlot[] = $a[0];
				$arrayYDataPlot[] = $a[1];
			}

			$arrRound = array();
			foreach($arrayYDataPlot as $a) {
				$arrRound[] = round($a, 2);
			}

			$arrayYDataPlot = $arrRound;

			$px = $this->generateJSONVar($arrayXDataPlot, 'number');
			$py = $this->generateJSONVar($arrayYDataPlot, 'number');

			$fl = ltrim($fl);
			$fl = rtrim($fl);
			$fl = preg_split('/\s+/', $fl);
			$fl = explode("-", $fl[1]);
			$rp1 = $fl[0];
			$rp2 = $fl[1];

			sort($arrayYDataPlot);
			$adistsort = $this->generateJSONVar($arrayYDataPlot, 'number');

			$json = '{';
			$json .= '"px":'.$px.',';
			$json .= '"py":'.$py.',';
			$json .= '"rp1":"'.$rp1.'",';
			$json .= '"rp2":"'.$rp2.'",';
			$json .= '"adistsort":'.$adistsort;
			$json .= '}';

			return($json);

		}else{

			$this->logger->error("WEB - File not found", [$path.$fileName]);
			return '{"error": true}';

		}

	}

	private function getEndToEndJSON($path, $path_pl) {

		$fileName = "distances.dat";

		if(file_exists($path.$fileName)) {

			chdir($path);
			
			$arrayRows = file($fileName);
			$fl = array_shift($arrayRows);
			$arrayXDataPlot = array();
			$arrayYDataPlot = array();
			foreach($arrayRows as $a) {
				$a = ltrim($a);
				$a = rtrim($a);
				$a = preg_split('/\s+/', $a);
				$arrayXDataPlot[] = $a[0];
				$arrayYDataPlot[] = $a[1];
			}

			$arrRound = array();
			foreach($arrayYDataPlot as $a) {
				$arrRound[] = round($a, 2);
			}

			$arrayYDataPlot = $arrRound;

			$px = $this->generateJSONVar($arrayXDataPlot, 'number');
			$py = $this->generateJSONVar($arrayYDataPlot, 'number');

			$fl = ltrim($fl);
			$fl = rtrim($fl);
			$fl = preg_split('/\s+/', $fl);
			$fl = explode("-", $fl[1]);
			$rp1 = $fl[0];
			$rp2 = $fl[1];

			sort($arrayYDataPlot);
			$adistsort = $this->generateJSONVar($arrayYDataPlot, 'number');
			
			$pl = null;
			if(file_exists($path_pl)) {
				chdir($path_pl);
				$pl = trim(shell_exec("grep Persistence PL.out"));
			}

			$json = '{';
			$json .= '"px":'.$px.',';
			$json .= '"py":'.$py.',';
			$json .= '"rp1":'.$rp1.',';
			$json .= '"rp2":'.$rp2.',';
			$json .= '"adistsort":'.$adistsort.',';
			$json .= '"pl":"'.$pl.'"';
			$json .= '}';

			return($json);

		}else{

			$this->logger->error("WEB - File not found", [$path.$fileName]);
			return '{"error": true}';

		}

	}

	private function getCircularTable($path) {

		$fileName = "single_structure_circle.csv";

		if(file_exists($path.'/'.$fileName)) {

			chdir($path);
			$arrayPop = file($fileName);

			array_shift($arrayPop);

			$output = array();
			foreach($arrayPop as $a) {
				$a = rtrim($a);
				$v = preg_split('/\,/', $a);
				$output[] = [$v[0], $v[1]];
			}

			return $output;

		}else{

			$this->logger->error("WEB - File not found", [$path.'/'.$fileName]);
			return '{"error": true}';

		}

	}	

	private function getBendingTable($path) {

		$fileName = "single_structure.csv";

		if(file_exists($path.'/'.$fileName)) {

			chdir($path);
			$arrayPop = file($fileName);

			array_shift($arrayPop);

			$output = array();
			foreach($arrayPop as $a) {
				$a = rtrim($a);
				$v = preg_split('/\,/', $a);
				$output[] = [$v[0], $v[1]];
			}

			return $output;

		}else{

			$this->logger->error("WEB - File not found", [$path.'/'.$fileName]);
			return '{"error": true}';

		}

	}

	private function getEnergyTable($path, $fileName) {

		if(file_exists($path.'/'.$fileName)) {

			chdir($path);
			$arrayPop = file($fileName);

			$output = array();
			foreach($arrayPop as $a) {
				$a = rtrim($a);
				$a = str_replace("\"", "", $a);
				$v = preg_split('/\,/', $a);
				$output[] = [$v[0], $v[1], $v[2]];
			}

			return $output;

		}else{

			$this->logger->error("WEB - File not found", [$path.'/'.$fileName]);
			return '{"error": true}';

		}

	}

	private function getEnergyProtTable($path, $fileName, $proteins) {

		if(file_exists($path.'/'.$fileName)) {

			chdir($path);
			$arrayPop = file($fileName);

			$output = array();
			foreach($arrayPop as $a) {
				$a = rtrim($a);
				$a = str_replace("\"", "", $a);
				$v = preg_split('/\,/', $a);

				$output[] = [strtoupper($proteins[$v[0] - 1]->code), $v[1], $v[2]];
			}

			return $output;

		}else{

			$this->logger->error("WEB - File not found", [$path.'/'.$fileName]);
			return '{"error": true}';

		}

	}

	private function getCircularScatterPlotJSON($path, $subsection, $type, $flag) {
		
		$fileName = $type.".csv";

		if(file_exists($path.'/'.$fileName)) {

			chdir($path);
			$arrayPop = file($fileName);

			array_shift($arrayPop);

			$arrayValues = array();
			$arrayValuesX = array();
			$arrayValuesY = array();
			foreach($arrayPop as $a) {
				$a = rtrim($a);
				$v = preg_split('/\,/', $a);
				array_shift($v);
				$arrayValues[] = $v;
			}

			$arrayValuesX = $arrayValues[0];
			$arrayValuesY = $arrayValues[1];

			$x = $this->generateJSONVar($arrayValuesX, 'number');
			$y = $this->generateJSONVar($arrayValuesY, 'number');

			$json = '{';
			$json .= '"x":'.$x.',';
			$json .= '"y":'.$y;
			$json .= '}';

			return($json);

		}else{

			$this->logger->error("WEB - File not found", [$path.'/'.$fileName]);
			return '{"error": true}';

		}

	}

	private function getStiffnessScatterPlotJSON($path, $subsection, $type, $flag) {
		
		$fileName = $type.".dat";

		if(file_exists($path.'/'.$fileName)) {

			chdir($path);
			$arrayPop = file($fileName);

			$arrayLabels = array();
			$arrayValues1 = array();
			$c = 1;
			foreach($arrayPop as $a) {
				$a = rtrim($a);
				$v = preg_split('/\s+/', $a);
				if(($v[0] != '?') || ($v[1] != '?')) {
					$arrayLabels[] = $c.' - '.$v[0];
					$auxval = $v[1];
					if($v[1] == "NaN") $auxval = "null";
					$arrayValues1[] = $auxval;
				}
				$c ++;
			}

			$lb = $this->generateJSONVar($arrayLabels, 'string');
			$v1 = $this->generateJSONVar($arrayValues1, 'number');

			$json = '{';
			$json .= '"labels":'.$lb.',';
			$json .= '"v1":'.$v1;
			//$json .= '"v2":'.$v2.',';
			//$json .= '"e2":'.$e2;
			$json .= '}';

			return($json);

		}else{

			$this->logger->error("WEB - File not found", [$path.'/'.$fileName]);
			return '{"error": true}';

		}

	}

	private function getCurvesBarsPlotJSON($path, $subsection, $type, $cols) {
		
		$fileName = $type.".dat";

		if(file_exists($path.$subsection.'/'.$fileName)) {

			chdir($path.$subsection);
			$arrayPop = file($fileName);

			if($cols == 4) array_shift($arrayPop);

			$arrayLabels = array();
			$arrayValues1 = array();
			$arrayValues2 = array();
			$arrayValues3 = array();
			$arrayValues4 = array();
			foreach($arrayPop as $a) {
				$a = rtrim($a);
				$v = preg_split('/\s+/', $a);
				$arrayLabels[] = $v[1].'-'.$v[0];
				$arrayValues1[] = intval($v[2]);
				if($cols == 4) {
					$arrayValues2[] = intval($v[3]);
					$arrayValues3[] = intval($v[4]);
					$arrayValues4[] = intval($v[5]);
				}else{
					$arrayValues2[] = 100 - intval($v[2]);
				}
			}

			$cutoff = (sizeof($arrayValues1) / 2) + 1;

			$lb = $this->generateJSONVarWithCutoff($arrayLabels, $cutoff, '" | "');

			$x1 = $this->generateJSONVarWithCutoff($arrayValues1, $cutoff, '0');
			$x2 = $this->generateJSONVarWithCutoff($arrayValues2, $cutoff, '0');

			if($cols == 4) {
		
				$x3 = $this->generateJSONVarWithCutoff($arrayValues3, $cutoff, '0');
				$x4 = $this->generateJSONVarWithCutoff($arrayValues4, $cutoff, '0');

			}

			$json = '{';
			$json .= '"labels":'.$lb.',';
			$json .= '"x1":'.$x1.',';
			$json .= '"x2":'.$x2;
			if($cols == 4) {
				$json .= ',"x3":'.$x3.',';
				$json .= '"x4":'.$x4;
			}
			$json .= '}';

			return($json);
			
		}else{

			$this->logger->error("WEB - File not found", [$path.$subsection.'/'.$fileName]);
			return '{"error": true}';

		}

	}

	private function getCurvesScatterPlotJSON($path, $subsection, $type, $cols) {

		$fileName = $type.".dat.gnuplot";
		$fileName2 = $type.".dat";

		if(file_exists($path.$subsection.'/'.$fileName)) {

			chdir($path.$subsection);
			$arrayPop = file($fileName);

			$firstc = fopen($fileName2,"r");
			$fc = fgetc($firstc);
			fclose($firstc);

			$arrayLabels = array();
			$arrayValues1 = array();
			$arrayValues2 = array();
			$arrayValues3 = array();
			$arrayErrors1 = array();
			$c = intval($fc);
			foreach($arrayPop as $a) {
				$a = rtrim($a);
				$v = preg_split('/\s+/', $a);
				//if(($v[3] != '?') || ($v[4] != '?')) {

					$lab = $v[0];
					if($subsection == "grooves") $lab = substr($v[0], 0, 2);
					if($subsection == "helical_bpstep") $lab = substr_replace($v[0], ":", 2, 0);

					$arrayLabels[] = $c.'-'.$lab;

					$auxval = $v[1];
					if($v[1] == "NaN") $auxval = "null";
					$arrayValues1[] = $auxval;

					$auxval = $v[2];
					if($v[2] == "NaN") $auxval = "null";
					$arrayErrors1[] = $auxval;

					$auxval = $v[3];
					if($v[3] == "NaN") $auxval = "null";
					$arrayValues2[] = $auxval;

					$auxval = $v[4];
					if($v[4] == "NaN") $auxval = "null";
					$arrayValues3[] = $auxval;
					$c ++;
				//}
			}

			$lb = $this->generateJSONVar($arrayLabels, 'string');
			$v1 = $this->generateJSONVar($arrayValues1, 'number');
			$e1 = $this->generateJSONVar($arrayErrors1, 'number');
			$v2 = $this->generateJSONVar($arrayValues2, 'number');
			$v3 = $this->generateJSONVar($arrayValues3, 'number');

			$json = '{';
			$json .= '"labels":'.$lb.',';
			$json .= '"v1":'.$v1.',';
			$json .= '"e1":'.$e1;
			if($cols == 2) {
				$json .= ',"v2":'.$v2;
			}
			if($cols == 3) {
				$json .= ',"v2":'.$v2;
				$json .= ',"v3":'.$v3;
			}
			$json .= '}';

			return($json);

		}else{

			$this->logger->error("WEB - File not found", [$path.$subsection.'/'.$fileName]);
			return '{"error": true}';

		}

	}

	public function getSpecificOutput($request, $response, $args) {
	
		$projectID = $args["id"];
		$strtype = $args["strtype"];
		$section = $args["section"];
		$subsection = $args["subsection"];
		$type = $args["type"];

		$projectData = $this->getData($args['id']);

		if($projectData->resolution == 0) $res = "CG";
		else $res = "AA";

		switch($section) {

			case 'contacts-dist':
				$path = $this->global['filesPath']
				.$projectData->folder
				.sprintf($this->global['flex']['folder'.$strtype], $res)
				.$this->global['NAFlex']['naflex'.$strtype]
				.$this->global['contacts']['folder'.$strtype]
				.'PROTpi-PROTpi/';
				echo $this->getContactsDistJSON($path, $type);
			break;

			case 'contacts':
				$path = $this->global['filesPath']
				.$projectData->folder
				.sprintf($this->global['flex']['folder'.$strtype], $res)
				.$this->global['NAFlex']['naflex'.$strtype]
				.$this->global['contacts']['folder'.$strtype];
				echo $this->getContactsJSON($path, $subsection, $type);
			break;

			case 'curves':
				$path = $this->global['filesPath']
				.$projectData->folder
				.sprintf($this->global['flex']['folder'.$strtype], $res)
				.$this->global['NAFlex']['naflex'.$strtype]
				.$this->global['curves']['folder'];
				echo $this->getCurvesJSON($path, $subsection, $type);
			break;

			case 'pcazip':
				$path = $this->global['filesPath']
				.$projectData->folder
				.sprintf($this->global['flex']['folder'.$strtype], $res)
				.$this->global['NAFlex']['naflex'.$strtype]
				.$this->global['pcazip']['folder'];
				echo $this->getPCAZipJSON($path, $section, $subsection);
			break;

			case 'stiffness':
				$path = $this->global['filesPath']
				.$projectData->folder
				.sprintf($this->global['flex']['folder'.$strtype], $res)
				.$this->global['NAFlex']['naflex'.$strtype]
				.$this->global['stiffness']['folder'];
				echo $this->getStiffnessJSON($path, $subsection, $type);
			break;

			case 'circular':
				$path = $this->global['filesPath']
				.$projectData->folder
				.sprintf($this->global['flex']['folder'.$strtype], $res)
				.$this->global['circular']['folder'];
				echo $this->getCircularJSON($path, $subsection, $type, $strtype);
			break;

			case 'bending':
				$path = $this->global['filesPath']
				.$projectData->folder
				.sprintf($this->global['flex']['folder'.$strtype], $res)
				.$this->global['bending']['folder'];
				echo $this->getBendingJSON($path, $subsection, $type, $strtype);
			break;

			case 'end-to-end':
				$path = $this->global['filesPath']
				.$projectData->folder
				.sprintf($this->global['flex']['folder'.$strtype], $res)
				.$this->global['NAFlex']['naflex'.$strtype]
				.$this->global['end-to-end']['folder'];

				$path_pl =  $this->global['filesPath']
				.$projectData->folder
				.sprintf($this->global['flex']['folder'.$strtype], $res)
				.$this->global['end-to-end']['pl'];
				echo $this->getEndToEndJSON($path, $path_pl);
			break;

			case 'energy':
				$path = $this->global['filesPath']
				.$projectData->folder
				.sprintf($this->global['flex']['folder'.$strtype], $res)
				.$this->global['energy']['folder'];

				if(isset($projectData->proteins)) $proteins = $projectData->proteins;

				echo $this->getEnergyJSON($path, $subsection, $type, $strtype, $proteins);
			break;

			case 'sasa':
				$path = $this->global['filesPath']
				.$projectData->folder
				.sprintf($this->global['flex']['folder'.$strtype], $res)
				.$this->global['sasa']['folder'];

				if(isset($projectData->proteins)) $proteins = $projectData->proteins;

				$pathProt = $this->global['filesPath']
				.$projectData->folder.'/';

				$proteinsList = $this->getListOfProteins($pathProt);

				echo $this->getSasaJSON($path, $subsection, $type, $strtype, $proteins, $proteinsList);
			break;

			default:
			$this->logger->error("WEB - Analysis section not found", [$section]);
			return '{"error": true}';

		}

	}

	private function getListOfProteins($path) {

		chdir($path);

		if(file_exists($path.'proteins.info')) {
			$array = file($path.'proteins.info');
		} else {
			$this->logger->error("WEB - File not found", [$path.'proteins.info']);
		}

		return $array;

	}

	// NAFlex

	public function outputFlexContacts($request, $response, $args) {

		$projectData = $this->getData($args['id']);

		if(!$this->pageExists($projectData, $request, $response)) return false;

		$flextypes = $this->getFlexTypes($projectData->folder);

		$flexsubtypes = $this->getFlexSubTypes($projectData->folder, $args['strtype'], $projectData->tool);

		$firsttype = $this->getFirstFlexType($flextypes, $projectData->folder, $projectData->tool);

		$strtype = $args['strtype'];

		if($projectData->resolution == 0) $res = "CG";
		else $res = "AA";

		$pathProt = $this->global['filesPath']
			.$projectData->folder.'/';

		if($projectData->tool == 3) $proteinsList = $this->getListOfProteins($pathProt);

		if(isset($args['sample'])) $sample = true;

		$vars = [
			'page' => [
				'title' => 'Output Flexibility Analyses Contacts - '.$this->global['longProjectName'],
				'description' => $this->global['longProjectName'] . ' output flexibility analyses contacts',
				'basename' => 'flex'.$args['strtype'],
				'cookies' => $_COOKIE['cookie_consent'] === null ? true : false,
				'ga' => $_COOKIE['cookie_consent'] === 'accepted' ? true : false,
				'flexname' => 'contacts'
			],
			'id' => $args['id'],
			'projectData' => $projectData,
			'defaultFlexEQ' => $this->global['flextypes'][$firsttype[0]],
			'defaultFlexTRAJ' => $this->global['flextypes'][$firsttype[1]],
			'flextypes' => $flextypes,
			'flexsubtypes' => $flexsubtypes,
			'proteinsList' => $proteinsList,
			'strType' => $args['strtype'],
			'sample' => $sample
		];  

		$this->view->render($response, 'flex-contacts.html', $vars);

	}


	private function getPCATable($p, $e, $c, $s) {

		if(!file_exists($p.$e)) $this->logger->error("WEB - File not found", [$p.$e]);
   	if(!file_exists($p.$c)) $this->logger->error("WEB - File not found", [$p.$c]);
		if(!file_exists($p.$s)) $this->logger->error("WEB - File not found", [$p.$s]);

		$evaluation = file($p.$e);
		$collectivity = file($p.$c);
		$stiffness = file($p.$s);

		$array = array();

		//for ($i = 0; $i < sizeof($evaluation); $i++) {
		// MAX NUMBER OF VECTORS IS ALWAYS 10
		for ($i = 0; $i < 10; $i++) {
			$array[$i][] = ($i + 1);
			//$array[$i][] = rtrim($evaluation[$i]);
			$array[$i][] = substr($evaluation[$i], 0, -5);
			$array[$i][] = substr($collectivity[$i], 0, -5);
			$array[$i][] = substr($stiffness[$i], 0, -5);
		}

		return $array;

	}


	public function outputFlexPCAZip($request, $response, $args) {

		$projectData = $this->getData($args['id']);
		$strtype = $args['strtype'];

		if(!$this->pageExists($projectData, $request, $response)) return false;

		if($projectData->resolution == 0) $res = "CG";
		else $res = "AA";

		$path = $this->global['filesPath']
			.$projectData->folder
			.sprintf($this->global['flex']['folder'.$strtype], $res)
			.$this->global['NAFlex']['naflex'.$strtype]
			.$this->global['pcazip']['folder'];

		$PCATable = $this->getPCATable($path, 'NAFlex_pcazipOut.evals', 'NAFlex_pcazipOut.collectivity', 'NAFlex_pcazipOut.stiffness');

		$flextypes = $this->getFlexTypes($projectData->folder);

		$flexsubtypes = $this->getFlexSubTypes($projectData->folder, $args['strtype'], $projectData->tool);

		$firsttype = $this->getFirstFlexType($flextypes, $projectData->folder, $projectData->tool);

		if(isset($args['sample'])) $sample = true;

		$vars = [
			'page' => [
				'title' => 'Output Flexibility Analyses PCAZip - '.$this->global['longProjectName'],
				'description' => $this->global['longProjectName'] . ' output flexibility analyses PCAZip',
				'basename' => 'flex'.$args['strtype'],
				'cookies' => $_COOKIE['cookie_consent'] === null ? true : false,
				'ga' => $_COOKIE['cookie_consent'] === 'accepted' ? true : false,
				'flexname' => 'pcazip'
			],
			'id' => $args['id'],
			'projectData' => $projectData,
			'PCATable' => $PCATable,
			'defaultFlexEQ' => $this->global['flextypes'][$firsttype[0]],
			'defaultFlexTRAJ' => $this->global['flextypes'][$firsttype[1]],
			'flextypes' => $flextypes,
			'flexsubtypes' => $flexsubtypes,
			'strType' => $args['strtype'],
			'sample' => $sample
		];  

		$this->view->render($response, 'flex-pcazip.html', $vars);

	}

	public function outputFlexEndToEnd($request, $response, $args) {

		$projectData = $this->getData($args['id']);
		$strtype = $args['strtype'];

		if(!$this->pageExists($projectData, $request, $response)) return false;

		/*if($projectData->resolution == 0) $res = "CG";
		else $res = "AA";

		$path = $this->global['filesPath']
			.$projectData->folder
			.sprintf($this->global['flex']['folder'.$strtype], $res)
			.$this->global['NAFlex']['naflex'.$strtype]
			.$this->global['pcazip']['folder'];

		$PCATable = $this->getPCATable($path, 'NAFlex_pcazipOut.evals', 'NAFlex_pcazipOut.collectivity', 'NAFlex_pcazipOut.stiffness');*/

		$flextypes = $this->getFlexTypes($projectData->folder);

		$flexsubtypes = $this->getFlexSubTypes($projectData->folder, $args['strtype'], $projectData->tool);

		$firsttype = $this->getFirstFlexType($flextypes, $projectData->folder, $projectData->tool);

		if(isset($args['sample'])) $sample = true;

		$vars = [
			'page' => [
				'title' => 'Output Flexibility Analyses End-to-end - '.$this->global['longProjectName'],
				'description' => $this->global['longProjectName'] . ' output flexibility analyses end-to-end',
				'basename' => 'flex'.$args['strtype'],
				'cookies' => $_COOKIE['cookie_consent'] === null ? true : false,
				'ga' => $_COOKIE['cookie_consent'] === 'accepted' ? true : false,
				'flexname' => 'end-to-end'
			],
			'id' => $args['id'],
			'projectData' => $projectData,
			//'PCATable' => $PCATable,
			'defaultFlexEQ' => $this->global['flextypes'][$firsttype[0]],
			'defaultFlexTRAJ' => $this->global['flextypes'][$firsttype[1]],
			'flextypes' => $flextypes,
			'flexsubtypes' => $flexsubtypes,
			'strType' => $args['strtype'],
			'sample' => $sample
		];  

		$this->view->render($response, 'flex-end-to-end.html', $vars);

	}

	public function outputFlexStiffness($request, $response, $args) {

		$projectData = $this->getData($args['id']);

		if(!$this->pageExists($projectData, $request, $response)) return false;

		$flextypes = $this->getFlexTypes($projectData->folder);

		$flexsubtypes = $this->getFlexSubTypes($projectData->folder, $args['strtype'], $projectData->tool);

		$firsttype = $this->getFirstFlexType($flextypes, $projectData->folder, $projectData->tool);

		if(isset($args['sample'])) $sample = true;

		$vars = [
			'page' => [
				'title' => 'Output Flexibility Analyses Stiffness - '.$this->global['longProjectName'],
				'description' => $this->global['longProjectName'] . ' output flexibility analyses stiffness',
				'basename' => 'flex'.$args['strtype'],
				'cookies' => $_COOKIE['cookie_consent'] === null ? true : false,
				'ga' => $_COOKIE['cookie_consent'] === 'accepted' ? true : false,
				'flexname' => 'stiffness'
			],
			'id' => $args['id'],
			'projectData' => $projectData,
			'defaultFlexEQ' => $this->global['flextypes'][$firsttype[0]],
			'defaultFlexTRAJ' => $this->global['flextypes'][$firsttype[1]],
			'flextypes' => $flextypes,
			'flexsubtypes' => $flexsubtypes,
			'strType' => $args['strtype'],
			'sample' => $sample
		];  

		$this->view->render($response, 'flex-stiffness.html', $vars);

	}

	public function outputFlexCurves($request, $response, $args) {

		$projectData = $this->getData($args['id']);

		if(!$this->pageExists($projectData, $request, $response)) return false;

		$flextypes = $this->getFlexTypes($projectData->folder);

		$flexsubtypes = $this->getFlexSubTypes($projectData->folder, $args['strtype'], $projectData->tool);

		$firsttype = $this->getFirstFlexType($flextypes, $projectData->folder, $projectData->tool);

		if(isset($args['sample'])) $sample = true;

		$vars = [
			'page' => [
				'title' => 'Output Flexibility Analyses Curves - '.$this->global['longProjectName'],
				'description' => $this->global['longProjectName'] . ' output flexibility analyses curves',
				'basename' => 'flex'.$args['strtype'],
				'cookies' => $_COOKIE['cookie_consent'] === null ? true : false,
				'ga' => $_COOKIE['cookie_consent'] === 'accepted' ? true : false,
				'flexname' => 'curves'
			],
			'id' => $args['id'],
			'projectData' => $projectData,
			'defaultFlexEQ' => $this->global['flextypes'][$firsttype[0]],
			'defaultFlexTRAJ' => $this->global['flextypes'][$firsttype[1]],
			'flextypes' => $flextypes,
			'flexsubtypes' => $flexsubtypes,
			'strType' => $args['strtype'],
			'sample' => $sample
		];  

		$this->view->render($response, 'flex-curves.html', $vars);

	}

	public function outputFlexCircular($request, $response, $args) {

		$projectData = $this->getData($args['id']);

		if(!$this->pageExists($projectData, $request, $response)) return false;

		$flextypes = $this->getFlexTypes($projectData->folder);

		$flexsubtypes = $this->getFlexSubTypes($projectData->folder, $args['strtype'], $projectData->tool);

		$firsttype = $this->getFirstFlexType($flextypes, $projectData->folder, $projectData->tool);

		if(isset($args['sample'])) $sample = true;

		if($args['strtype'] == 'eq') {
			if($projectData->resolution == 0) $res = "CG";
			else $res = "AA";
			$path = $this->global['filesPath']
				.$projectData->folder
				.sprintf($this->global['flex']['folder'.$args['strtype']], $res)
				.$this->global['circular']['folder'];
			$eqTable = $this->getCircularTable($path);
		}

		$vars = [
			'page' => [
				'title' => 'Output Flexibility Analyses Circular - '.$this->global['longProjectName'],
				'description' => $this->global['longProjectName'] . ' output flexibility analyses circular',
				'basename' => 'flex'.$args['strtype'],
				'cookies' => $_COOKIE['cookie_consent'] === null ? true : false,
				'ga' => $_COOKIE['cookie_consent'] === 'accepted' ? true : false,
				'flexname' => 'circular'
			],
			'id' => $args['id'],
			'projectData' => $projectData,
			'defaultFlexEQ' => $this->global['flextypes'][$firsttype[0]],
			'defaultFlexTRAJ' => $this->global['flextypes'][$firsttype[1]],
			'flextypes' => $flextypes,
			'flexsubtypes' => $flexsubtypes,
			'strType' => $args['strtype'],
			'eqTable' => $eqTable,
			'sample' => $sample
		];  

		$this->view->render($response, 'flex-circular.html', $vars);

	}

	public function outputFlexBending($request, $response, $args) {

		$projectData = $this->getData($args['id']);

		if(!$this->pageExists($projectData, $request, $response)) return false;

		$flextypes = $this->getFlexTypes($projectData->folder);

		$flexsubtypes = $this->getFlexSubTypes($projectData->folder, $args['strtype'], $projectData->tool);

		$firsttype = $this->getFirstFlexType($flextypes, $projectData->folder, $projectData->tool);

		if($args['strtype'] == 'eq') {
			if($projectData->resolution == 0) $res = "CG";
			else $res = "AA";
			$path = $this->global['filesPath']
				.$projectData->folder
				.sprintf($this->global['flex']['folder'.$args['strtype']], $res)
				.$this->global['bending']['folder'];
			$eqTable = $this->getBendingTable($path);
		}

		if(isset($args['sample'])) $sample = true;

		$vars = [
			'page' => [
				'title' => 'Output Flexibility Analyses Bending - '.$this->global['longProjectName'],
				'description' => $this->global['longProjectName'] . ' output flexibility analyses bending',
				'basename' => 'flex'.$args['strtype'],
				'cookies' => $_COOKIE['cookie_consent'] === null ? true : false,
				'ga' => $_COOKIE['cookie_consent'] === 'accepted' ? true : false,
				'flexname' => 'bending'
			],
			'id' => $args['id'],
			'projectData' => $projectData,
			'defaultFlexEQ' => $this->global['flextypes'][$firsttype[0]],
			'defaultFlexTRAJ' => $this->global['flextypes'][$firsttype[1]],
			'flextypes' => $flextypes,
			'resolution' => $res,
			'flexsubtypes' => $flexsubtypes,
			'strType' => $args['strtype'],
			'eqTable' => $eqTable,
			'sample' => $sample
		];  

		$this->view->render($response, 'flex-bending.html', $vars);

	}

	public function outputFlexEnergy($request, $response, $args) {

		$projectData = $this->getData($args['id']);

		if(!$this->pageExists($projectData, $request, $response)) return false;

		$flextypes = $this->getFlexTypes($projectData->folder);

		$flexsubtypes = $this->getFlexSubTypes($projectData->folder, $args['strtype'], $projectData->tool);

		$firsttype = $this->getFirstFlexType($flextypes, $projectData->folder, $projectData->tool);

		if($projectData->resolution == 0) $res = "CG";
		else $res = "AA";
		$path = $this->global['filesPath']
			.$projectData->folder
			.sprintf($this->global['flex']['folder'.$args['strtype']], $res)
			.$this->global['energy']['folder'];
		$enTable = $this->getEnergyTable($path, "total_elastic_energy_meansd.csv");

		if($projectData->tool == 3) {
			$meansdTable = $this->getEnergyTable($path, "total_elastic_energy_unbound_meansd.csv");
			$dnaprotTable = $this->getEnergyProtTable($path, "elastic_energy_meansd_dnaprot.csv", $projectData->proteins);
		}

		if(isset($args['sample'])) $sample = true;

		$vars = [
			'page' => [
				'title' => 'Output Flexibility Analyses Energy - '.$this->global['longProjectName'],
				'description' => $this->global['longProjectName'] . ' output flexibility analyses energy',
				'basename' => 'flex'.$args['strtype'],
				'cookies' => $_COOKIE['cookie_consent'] === null ? true : false,
				'ga' => $_COOKIE['cookie_consent'] === 'accepted' ? true : false,
				'flexname' => 'energy'
			],
			'id' => $args['id'],
			'projectData' => $projectData,
			'defaultFlexEQ' => $this->global['flextypes'][$firsttype[0]],
			'defaultFlexTRAJ' => $this->global['flextypes'][$firsttype[1]],
			'flextypes' => $flextypes,
			'resolution' => $res,
			'flexsubtypes' => $flexsubtypes,
			'strType' => $args['strtype'],
			'enTable' => $enTable,
			'meansdTable' => $meansdTable,
			'dnaprotTable' => $dnaprotTable,
			'sample' => $sample
		];  

		$this->view->render($response, 'flex-energy.html', $vars);

	}

	public function outputFlexSASA($request, $response, $args) {

		$projectData = $this->getData($args['id']);
		$strtype = $args['strtype'];

		if(!$this->pageExists($projectData, $request, $response)) return false;

		$flextypes = $this->getFlexTypes($projectData->folder);

		$flexsubtypes = $this->getFlexSubTypes($projectData->folder, $args['strtype'], $projectData->tool);

		$firsttype = $this->getFirstFlexType($flextypes, $projectData->folder, $projectData->tool);

		/*$pathProt = $this->global['filesPath']
			.$projectData->folder.'/';

		$proteinsList = $this->getListOfProteins($pathProt);*/

		if(isset($args['sample'])) $sample = true;

		$vars = [
			'page' => [
				'title' => 'Output Flexibility Analyses SASA - '.$this->global['longProjectName'],
				'description' => $this->global['longProjectName'] . ' output flexibility analyses Sasa',
				'basename' => 'flex'.$args['strtype'],
				'cookies' => $_COOKIE['cookie_consent'] === null ? true : false,
				'ga' => $_COOKIE['cookie_consent'] === 'accepted' ? true : false,
				'flexname' => 'sasa'
			],
			'id' => $args['id'],
			'projectData' => $projectData,
			//'PCATable' => $PCATable,
			'defaultFlexEQ' => $this->global['flextypes'][$firsttype[0]],
			'defaultFlexTRAJ' => $this->global['flextypes'][$firsttype[1]],
			'flextypes' => $flextypes,
			'flexsubtypes' => $flexsubtypes,
			//'proteinsList' => $proteinsList,
			'strType' => $args['strtype'],
			'sample' => $sample
		];  

		$this->view->render($response, 'flex-sasa.html', $vars);

	}

	public function getStatus($request, $response, $args) {

		$projectData = $this->getData($args['id']);

		$status = $this->sge->status($projectData->pid);

		echo '{ "status":'.$status.' }';

	}

	public function downloadFile($request, $response, $args) {

		$projectData = $this->getData($args['id']);

		chdir($this->global['filesPath'].$projectData->folder);

		$str = glob("*.tgz");

		//$this->utils->downloadFile($this->global['filesPath'].$projectData->folder.'/'.$this->global['summary'][$args['kind'].'FileDown']);
		$this->utils->downloadFile($this->global['filesPath'].$projectData->folder."/$str[0]");

	}

	public function JSGlobals($request, $response, $args) {

		// samples list

		$samples = $this->global['sample'];

		$c = 1;
		foreach($samples as $s)	{
			echo "var sample$c='".$s[0]."';\n";
			$c ++;
		}
		
		echo "\n";

		return $response->withHeader('Content-type', 'application/javascript');
	
	}

}
