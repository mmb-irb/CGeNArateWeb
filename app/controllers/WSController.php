<?php
namespace App\Controllers;

class WSController extends Controller {

	protected $uploadsID = '';
	protected $stringData = '';

	// change status of recently finished jobs from 5 to 1
	private function cleanFinishedJobs() {

		$owner = $_SESSION['user']['id']; 

		$finishJobs = $this->jobs->getFinishedJobs($owner);

		$projectsList = array();

		foreach($finishJobs as $job){

			$projectID = $job->_id;

			$this->files->updateStatus($projectID, 1);
			$this->jobs->updateStatus($projectID, 1); 

		}

	
	}	

	// check if the running jobs have changed their status
	private function checkRunningJobs() {

		$owner = $_SESSION['user']['id']; 

		$runningJobs = $this->jobs->getRunningJobs($owner);

		//var_dump($runningJobs);

		if(!empty($runningJobs)) {

			foreach($runningJobs as $job){

				$projectID = $job->_id;

				$status= $this->sge->status($job->pid);
				
				if($status != 4) {

					$this->files->updateStatus($projectID, $status);
					$this->jobs->updateStatus($projectID, $status);

					if($status == 5) {

						$this->files->generateTree($this->global['filesPath'].$this->files->getPath($projectID), $owner);

						$this->meta->updateFiles($projectID, $owner);

					}

				}

			}
				
		}

	}

	// gets a list of all the projects status
	private function checkProjectsStatus() {

		$owner = $_SESSION['user']['id']; 

		$userJobs = $this->jobs->getUserJobs($owner, 20);

		$projectsList = array();

		foreach($userJobs as $job){

			$projectID = $job->_id;
			$status = $job->status;
			$category = $this->meta->getProjectCategory($job->_id);

			switch($category) {

				case 'pdbligand':
				case 'ligand':
				case 'upload':

					$pdb = $this->meta->getProjectPDB($projectID);
					$lig = $this->meta->getProjectLig($projectID);
	
					if(isset($pdb)) $inputs = $lig.' - '.$pdb;
					else $inputs = $lig;

					break;

				case 'smile':
					$inputs = 'SMILES';
					break;

			}

			$data = [
				"id" => $job->_id,
				"name" => $this->files->getData($projectID)->name,
				"status" => $job->status,
				"inputs" => $inputs
			];
			
			$projectsList[] = $data;

		}

		return $projectsList;

	}

	// gets a list of the recently finished Jobs
	private function checkProjectsFinished() {

		$owner = $_SESSION['user']['id']; 

		$finishJobs = $this->jobs->getFinishedJobs($owner);

		$projectsList = array();

		foreach($finishJobs as $job){

			$projectID = $job->_id;

			$data = [
				"id" => $job->_id,
				"name" => $this->files->getData($projectID)->name
			];
			
			$projectsList[] = $data;

		}

		return $projectsList;

	}

	private function jobStatus() {

		$this->cleanFinishedJobs();

		$this->checkRunningJobs();

		$p = [
			'projectsList' => $this->checkProjectsStatus(),
			'projectsFinished' => $this->checkProjectsFinished()
		];

		return $p;	
	
	}

	private function getDiskQuota() {

		$dq = $this->user->getDiskQuota($_SESSION['user']['id']);
		$du = $this->user->getDiskUsed($_SESSION['user']['id']);
		$totalDisk = $this->utils->getSize($dq);
		$usedDisk = $this->utils->getSize($du);
		$percentDisk = ($du/$dq)*100;

		$d = [
			'totalDisk' => $totalDisk,
			'usedDisk' => $usedDisk,
			'percentDisk' => $percentDisk,
		];

		return $d;

	}

	// entry page
	public function workspace($request, $response, $args) {

		$projects = $this->jobStatus();

		$disk = $this->getDiskQuota();

		$vars = [
			'page' => [
				'title' => 'User Workspace - '.$this->global['longProjectName'],
				'description' => 'In this page you will handle all the data',
				'basename' => 'workspace'
			],
			'uid' => $_SESSION['user']['id'],
			'projects' => $projects['projectsList'],
			'projectsFinished' => $projects['projectsFinished'],
			'defaultOutput' => $this->global['defaultOutput'],
			'disk' => $disk
		];  

		$this->view->render($response, 'workspace.html', $vars);

	}

	private function childrenToJSON($uid) {

		// get $uid children
		$children = $this->files->getDirChildren($uid)->children;

		// if $uid has children
		$length = sizeof($children);
		if($length > 0) {

			foreach($children as $dirID) {

				$dir = $this->files->getData($dirID);

				// reading all children and getting those of type dir
				if(($dir->type === "dir") && ($dir->status != 0)){

					$id = $dir->_id;
					$name = $dir->name;
					$status = $dir->status;

					// getting the _uploads _id and changing folder name
					/*if($name == $this->global['uploads']) {
						$this->uploadsID = $id;
						//$name = 'uploads';
					}*/

					/*
					 * status = 0 :: folder disabled
					 * status = 1 :: folder ok 
					 * status = 2 :: lack of metadata (not clickable)
					 * status = 3 :: error
					 * status = 4 :: running
					 * status = 5 :: finished (or go to status = 1?) ?????
					 */

					switch($status) {
						case 0: $statusIcon = '"state": { "disabled": true },';
						break;
						case 1: $statusIcon = '';
						break;
						case 2: $statusIcon = '"icon" : "fa fa-exclamation-triangle icon-state-warning icon-lg",';
						break;
						case 3: $statusIcon = '"icon" : "fa fa-times-circle icon-state-danger icon-lg",';
						break;
						case 4: $statusIcon = '"icon" : "fa fa-cog fa-spin icon-state-success icon-lg",';
						break;
						case 5: $statusIcon = '"icon" : "fa fa-folder icon-state-success icon-lg",';
						break;

					}

					$this->stringData .= '{
								"text": "'.$name.'",
									"id": "'.$id.'",'.
									$statusIcon
								.'"children": [';


					// recursive call to find if this folder has childs
					if(($status == 1) || ($status == 5))  $this->stringData .= $this->childrenToJSON($id);
					
					$this->stringData .= ']
					},';

				}

			}

		} 
	
	}

	public function getTree($request, $response, $args) {

		$id = $args['id'];

		$dirID = $this->files->getDirID($id, $_SESSION['user']['id']);

		echo 
		'{
		"datatree":
			{
				"id": "'.$dirID.'",
				"text": "User Workspace",
				"children": [';

				$this->childrenToJSON($this->files->getRootDir($id)->_id);

				$this->stringData .= ']';

				echo str_replace('},]', '}]', $this->stringData);

		/*echo '
				},
		"uploadsID": "'.$this->uploadsID.'"
		}';*/

		echo '
				}
		}';

	}


	private function getMetadataLink($name) {

		$n = explode(".", $name);

		$t = explode("_", $n[0]);

		if(sizeof($t) == 1) return 'meta/pdb/'.$t[0];
		else return 'meta/mon/'.$t[0];

	}

	public function getTable($request, $response, $args) {

		$folderID = $args['id'];

		$folderData = $this->files->getData($folderID);

		$folderName = $folderData->name;
		$folderStatus = $folderData->status;
		$isProject = $folderData->isProject;

		$children = $this->files->getDirChildren($folderID)->children;

		if($folderName == $_SESSION['user']['id']) $folderName = "User Workspace";

		echo '
				<div class="portlet-title">
						<div class="caption">
								<i class="fa fa-files-o font-green"></i> 
								<span class="caption-subject font-green sbold uppercase">Content of <span class="font-yellow-mint" style="font-weight:bold;">'.$folderName.'</span> folder</span>
						</div>';

						echo '<div class="actions">';
						
							if(($isProject) && (($folderStatus == 1) || ($folderStatus == 5))) {
								echo '<a href="'.$this->global['baseURL'].$this->global['defaultOutput'].$folderID.'" class="btn btn-sm btn-warning yellow-mint" style="margin-right:10px;"><i class="fa fa-file-text" style="margin-right:5px;"></i> View summary</a>';
							}
			
							echo '<div class="btn-group">';

							if(($folderStatus == 1) || ($folderStatus == 5)) {
		
								if($isProject) {

									echo '<a class="btn grey-cascade btn-sm btn-outline" href="javascript:;" data-toggle="dropdown">
									<i class="fa fa-calendar-check-o"></i> Project actions
									<i class="fa fa-angle-down"></i>
									</a>
									<ul class="dropdown-menu pull-right">
										<li>
												<a href="'.$this->global['baseURL'].'download/folder/'.$folderID.'">
														<i class="fa fa-download"></i> Download project </a>
										</li>
											</li>
											<li>
													<a href="'.$this->global['baseURL'].$this->global['defaultOutput'].$folderID.'">
															<i class="fa fa-file-text"></i> View summary </a>
											</li>
											<li>
													<a href="javascript:showMeta(\''.$folderID.'\', \''.$folderName.'\');">
															<i class="fa fa-tags"></i> Show project metadata </a>
											</li>
											<li>
													<a href="javascript:deleteFolder(\''.$folderID.'\', \''.$folderName.'\');">
															<i class="fa fa-trash"></i> Delete project</a>
											</li>
									</ul>';


								} else {

									echo '<a class="btn grey-cascade btn-sm btn-outline" href="javascript:;" data-toggle="dropdown">
									<i class="fa fa-folder"></i> Folder actions
									<i class="fa fa-angle-down"></i>
									</a>
									<ul class="dropdown-menu pull-right">
										<li>
												<a href="'.$this->global['baseURL'].'download/folder/'.$folderID.'">
														<i class="fa fa-download"></i> Download folder </a>
										</li>';
									/*
									if($folderName != $this->global['uploads']) {
									echo '<li>
													<a href="javascript:deleteFolder(\''.$folderID.'\', \''.$folderName.'\');">
															<i class="fa fa-trash"></i> Delete Folder</a>
											</li>';
									}
									*/
									echo '</ul>';


								}

							} elseif($folderStatus == 2) {
								echo '
									<a class="btn grey-cascade btn-sm btn-outline" href="javascript:cancelJob(\''.$folderID.'\', \''.$folderName.'\');" style="margin-right:10px;">
										<i class="fa fa-times"></i> Cancel job
									</a>									
									<a class="btn yellow-mint btn-sm btn-outline" href="'.$this->global['baseURL'].'upload/step2/'.$folderID.'">
										<i class="fa fa-pencil-square-o"></i> Add metadata 
									</a>';
							}elseif($folderStatus == 3) {
									echo '<a class="btn grey-cascade btn-sm btn-outline" href="javascript:deleteJob(\''.$folderID.'\');">
										<i class="fa fa-trash"></i> Delete project
									</a>';
							}elseif($folderStatus == 4) {
									echo '<a class="btn grey-cascade btn-sm btn-outline" href="javascript:cancelJob(\''.$folderID.'\', \''.$folderName.'\');">
										<i class="fa fa-times"></i> Cancel job
									</a>';
							}
						echo '</div>
					</div>
			</div>
			<div class="portlet-body">';


		$length = sizeof($children);
		$c = 0;

		if(($length > 0) && (($folderStatus == 1) || ($folderStatus == 5))) {

			echo '<table class="table table-striped table-bordered table-hover table-checkable order-column" id="ws-table">
							<thead>
									<tr>
											<th>
													<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
															<input type="checkbox" class="group-checkable" data-set="#ws-table .checkboxes" />
															<span></span>
													</label>
											</th>
											<th> File </th>
											<th> Format </th>
											<th> Date </th>
											<th> Size </th>
											<th> Actions </th>
									</tr>
							</thead>
							<tbody>';

			foreach($children as $fileID) {

				$file = $this->files->getData($fileID);
				$extension = strtoupper($this->utils->getExtension($file->name));

				if($file->type == "file") {
				
					echo '
					<tr>
							<td>
									<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
											<input type="checkbox" class="checkboxes" value="1" />
											<span></span>
									</label>
							</td>';

							$plainExtensions = array("md", "txt", "log", "smi");
							$nglExtensions = array("pdb", "gro", "mol2", "ent");
							$chemExtensions = array("mol");

							if (in_array($this->utils->getExtension($file->name), $plainExtensions)) {
								echo '<td> <a href="'.$this->global['baseURL'].'open/file/'.$file->_id.'" target="_blank" class="font-green">'.$file->name.'</a> </td>';
							}elseif (in_array($this->utils->getExtension($file->name), $nglExtensions)) {

								/*if(($this->files->getNameFromPath($file->parent) == 'inputs') || (strrpos($file->name, "pdb") === 0)) $extension = 'input';
								else $extension = $this->utils->getExtension($file->name);*/

								$filename = pathinfo(strtolower($file->name))["filename"];

								if(strrpos($filename, 'pdb') === 0) $type = 'pdb';
								elseif(strrpos($filename, 'lig') === 0) $type = 'lig';
								else $type = 'default';

								echo '<td> <a href="javascript:openNGL(\''.$file->_id.'\', \''.$file->name.'\', \''.$type.'\');" class="font-green">'.$file->name.' </a></td>';
							}elseif (in_array($this->utils->getExtension($file->name), $chemExtensions)) {
								echo '<td> <a href="javascript:openChemViewer(\''.$file->_id.'\', \''.$file->name.'\');" class="font-green">'.$file->name.' </a></td>';
							}else {
								echo '<td> '.$file->name.' </td>';
							}


							echo '<td> '.$extension.' </td>
							<td> '.$this->utils->getHumanDate($file->mtime->sec).' </td>
							<td> '.$this->utils->getSize($file->size).'  </td>
							<td>
									<div class="btn-group">
											<button class="btn btn-xs yellow-mint dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> Actions
													<i class="fa fa-angle-down"></i>
											</button>
											<ul class="dropdown-menu pull-center" role="menu">
													<li>
															<a href="'.$this->global['baseURL'].'download/file/'.$file->_id.'">
																	<i class="fa fa-download"></i> Download file </a>
													</li>';
							
													if(($folderName == $this->global['uploads']) && (!$file->running)) {

														echo '<li>
																<a href="javascript:deleteFile(\''.$file->_id.'\', \''.$file->name.'\');">
																		<i class="fa fa-trash"></i> Delete file </a>
														</li>';
		
													}

						//if($this->utils->getExtension($file->name) == 'pdb') {
						if (in_array($this->utils->getExtension($file->name), $nglExtensions)) {
		
							$filename = pathinfo(strtolower($file->name))["filename"];

							if(strrpos($filename, 'pdb') === 0) $type = 'pdb';
							elseif(strrpos($filename, 'lig') === 0) $type = 'lig';
							else $type = 'default';

						echo'					<li>
															<a href="javascript:openNGL(\''.$file->_id.'\', \''.$file->name.'\', \''.$extension.'\');">
																	<i class="fa fa-cube"></i> Visualize in 3D </a>
													</li>
													<li>
															<a href="'.$this->global['baseURL'].'open/file/'.$file->_id.'" target="_blank">
																	<i class="fa fa-file-text-o"></i> View plain text </a>
													</li>';

							/*$restrictedDirectories = array("inputs", "uploads");
							
							if (in_array($this->files->getNameFromPath($file->parent), $restrictedDirectories)) {	
		
								echo '		<li>
															<a href="'.$this->global['baseURL'].$this->getMetadataLink($file->name).'">
																	<i class="fa fa-tags"></i> View metadata </a>
													</li>';
							}*/
			
						}elseif (in_array($this->utils->getExtension($file->name), $chemExtensions)) {
	
							echo'					<li>
															<a href="javascript:openChemViewer(\''.$file->_id.'\', \''.$file->name.'\');">
																	<i class="fa fa-clone"></i> Visualize in 2D </a>
													</li>';
								
						}


						echo '
											</ul>
									</div>
							</td>
					</tr>';

				}else{


					switch($file->status){

						case 1:

							echo '
							<tr>
									<td>
										<a class="yellow-mint font-lg" href="javascript:openNode(\''.$file->_id.'\', \''.$file->parent.'\');"><i class="fa fa-folder"></i></a>
									</td>
									<td> <a class="yellow-mint" href="javascript:openNode(\''.$file->_id.'\', \''.$file->parent.'\');"><strong>'.$file->name.'</strong></a> </td>
									<td>  FOLDER </td>
									<td> '.$this->utils->getHumanDate($file->mtime->sec).' </td>
									<td> </td>
									<td>
											<div class="btn-group">
													<button class="btn btn-xs yellow-mint dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> Actions
															<i class="fa fa-angle-down"></i>
													</button>
													<ul class="dropdown-menu pull-center" role="menu">';
												if($file->isProject) {

												echo '<li>
																	<a href="'.$this->global['baseURL'].'download/folder/'.$file->_id.'">
																			<i class="fa fa-download"></i> Download project </a>
															</li>
															<li>
																	<a href="'.$this->global['baseURL'].$this->global['defaultOutput'].$file->_id.'">
																			<i class="fa fa-file-text"></i> View Summary </a>
															</li>
															<li>
																	<a href="javascript:showMeta(\''.$file->_id.'\', \''.$file->name.'\');">
																			<i class="fa fa-tags"></i> Show project metadata </a>
															</li>
															<li>
																	<a href="javascript:deleteFolder(\''.$file->_id.'\', \''.$file->name.'\');">
																			<i class="fa fa-trash"></i> Delete project </a>
															</li>';															
												
												} else {

												echo '<li>
																	<a href="'.$this->global['baseURL'].'download/folder/'.$file->_id.'">
																			<i class="fa fa-download"></i> Download folder </a>
															</li>';
												}
													
												echo '</ul>
											</div>
									</td>
							</tr>';

							break;

						case 2:

							echo '
							<tr>
									<td>
										<a class="font-yellow-crusta" href="javascript:openNode(\''.$file->_id.'\', \''.$file->parent.'\');"><i class="fa fa-exclamation-triangle"></i></a>
									</td>
									<td> <a class="font-yellow-crusta" href="javascript:openNode(\''.$file->_id.'\', \''.$file->parent.'\');"><strong>'.$file->name.'</strong></a> </td>
									<td>  FOLDER </td>
									<td> '.$this->utils->getHumanDate($file->mtime->sec).' </td>
									<td> </td>
									<td>
										<div class="btn-group">
													<button class="btn btn-xs yellow-crusta dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> Actions
															<i class="fa fa-angle-down"></i>
													</button>
													<ul class="dropdown-menu pull-center" role="menu">
															<li>
																	<a href="'.$this->global['baseURL'].'upload/step2/'.$file->_id.'">
																			<i class="fa fa-pencil-square-o"></i> Add metadata </a>
															</li>
															<li>
																	<a href="javascript:cancelJob(\''.$file->_id.'\', \''.$file->name.'\');">
																			<i class="fa fa-times"></i> Cancel Job </a>
															</li>
													</ul>
											</div>
									</td>
							</tr>';

							break;

						case 3:

							echo '
							<tr>
									<td>
										<a class="font-red-mint" href="javascript:openNode(\''.$file->_id.'\', \''.$file->parent.'\');"><i class="fa fa-times-circle"></i></a>
									</td>
									<td> <a class="font-red-mint" href="javascript:openNode(\''.$file->_id.'\', \''.$file->parent.'\');"><strong>'.$file->name.'</strong></a> </td>
									<td>  FOLDER </td>
									<td> '.$this->utils->getHumanDate($file->mtime->sec).' </td>
									<td> </td>
									<td>
										<div class="btn-group">
													<button class="btn btn-xs red-mint dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> Actions
															<i class="fa fa-angle-down"></i>
													</button>
													<ul class="dropdown-menu pull-center" role="menu">
															<li>
																	<a href="javascript:deleteJob(\''.$file->_id.'\');">
																			<i class="fa fa-trash"></i> Delete project </a>
															</li>
													</ul>
											</div>
									</td>
							</tr>';

							break;

						case 4:

							echo '
							<tr>
									<td>
										<i class="fa fa-cog font-green"></i>
									</td>
									<td class="font-green"> <strong>'.$file->name.'</strong> </td>
									<td>  FOLDER </td>
									<td> '.$this->utils->getHumanDate($file->mtime->sec).' </td>
									<td> </td>
									<td>
										<div class="btn-group">
													<button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> Actions
															<i class="fa fa-angle-down"></i>
													</button>
													<ul class="dropdown-menu pull-center" role="menu">
															<li>
																	<a href="javascript:cancelJob(\''.$file->_id.'\', \''.$file->name.'\');">
																			<i class="fa fa-times"></i> Cancel Job </a>
															</li>
													</ul>
											</div>
									</td>
							</tr>';

							break;

					case 5:

							echo '
							<tr>
									<td>
										<a class="font-green font-lg" href="javascript:openNode(\''.$file->_id.'\', \''.$file->parent.'\');"><i class="fa fa-folder"></i></a>
									</td>
									<td> <a class="font-green" href="javascript:openNode(\''.$file->_id.'\', \''.$file->parent.'\');"><strong>'.$file->name.'</strong></a> </td>
									<td>  FOLDER </td>
									<td> '.$this->utils->getHumanDate($file->mtime->sec).' </td>
									<td> </td>
									<td>
											<div class="btn-group">
													<button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> Actions
															<i class="fa fa-angle-down"></i>
													</button>
													<ul class="dropdown-menu pull-center" role="menu">';
												if($file->isProject) {

												echo '<li>
																	<a href="'.$this->global['baseURL'].'download/folder/'.$file->_id.'">
																			<i class="fa fa-download"></i> Download project </a>
															</li>
															<li>
																	<a href="'.$this->global['baseURL'].$this->global['defaultOutput'].$file->_id.'">
																			<i class="fa fa-file-text"></i> View Summary </a>
															</li>
															<li>
																	<a href="javascript:deleteFolder(\''.$file->_id.'\', \''.$file->name.'\');">
																			<i class="fa fa-trash"></i> Delete project </a>
															</li>';															
												
												} else {

												echo '<li>
																	<a href="'.$this->global['baseURL'].'download/folder/'.$file->_id.'">
																			<i class="fa fa-download"></i> Download folder </a>
															</li>';
												}
													
												echo '</ul>
											</div>
									</td>
							</tr>';

							break;

				}
			

				}

			}

			echo '
					</tbody>
        </table>
			';

		} else {

			if(($length == 0) && (($folderStatus == 1) || ($folderStatus == 5))) {

				echo 'There is no content in this folder.';

			}
			
			if($folderStatus == 2) {

				echo 'There is no metadata for this project. Please, click the button "Add metadata" on the top right corner to update it and start to run the job.';

			}elseif($folderStatus == 3) {

				echo 'There was some error executing the job.<br><br>Description of the error: Lorem ipsum dolor sit amet blahblah';

			}elseif($folderStatus == 4) {

				echo 'The job is currently running, it could take some hours, please be patient.';
			
			}

		}

		echo '
				</div>
		';


	}

	public function downloadFile($request, $response, $args) {

		$fileID = $args['id'];

		$this->utils->downloadFile($this->global['filesPath'].$this->files->getPath($fileID));

	}

	public function openFile($request, $response, $args) {

		$fileID = $args['id'];

		echo nl2br(file_get_contents($this->global['filesPath'].$this->files->getPath($fileID)));

	}


	public function deleteFile($request, $response, $args) {

		$fileID = $args['id'];

		$this->files->deleteFile($fileID);

	}


	public function downloadFolder($request, $response, $args) {

		$folderID = $args['id'];

		$folderPath = $this->global['filesPath'].$this->files->getPath($folderID);
		$tmpPath = $this->global['filesPath'].$_SESSION['user']['id'].'/.tmp';

		$this->utils->downloadFolder($folderPath, $tmpPath);
	}

	public function getFilePath($request, $response, $args) {
	
		$fileID = $args['id'];
	
		echo $this->files->getPath($fileID);	

	}

	public function getMolPath($request, $response, $args) {
	
		$fileID = $args['id'];
		$smiles = $args['smiles'];
		
		echo $this->mol->getPath($fileID, $smiles);	

	}


	public function deleteFolder($request, $response, $args) {

		$folderID = $args['id'];

		$this->files->deleteFolder($folderID, $_SESSION['user']['id']);

	}


	// remove user and all his files
	public function deleteUser($request, $response, $args) {

		$this->user->deleteUserComplete($args['id']);

	}

	// check if some job has recently finished
	public function checkFinishedJobs($request, $response, $args) {

		$owner = $_SESSION['user']['id']; 

		$runningJobs = $this->jobs->getRunningJobs($owner);

		if(!empty($runningJobs)) {

			foreach($runningJobs as $job){

				$projectID = $job->_id;

				$status= $this->sge->status($job->pid);
				
				if($status != 4) {
					
					echo '{"runningJobs":false, "jobFinished": true}';
					break;
					
				}

				echo '{"runningJobs":true}';

			}

		}else{

			echo '{"runningJobs":false, "jobFinished": false}';

		}

	}


	// get metadata for PDB
	public function getStructure($request, $response, $args) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, sprintf($this->global['api']['pdbinfo'], $args['id']));
		$json = curl_exec($ch);
		curl_close($ch);

		$json = json_decode($json);

		$referer = implode("", $request->getHeader('HTTP_REFERER'));

		//if(strrpos($referer, "/step1/search"))	{ 
			$search = true;
		/*}	else {
			$search = false;
			}*/

		$vars = [
			'page' => [
				'title' => 'PDB Structure Metadata - '.$this->global['longProjectName'],
				'description' => 'Metadata relative to PDB '.$args['id'],
				'basename' => 'pdbmeta'
			],
			'json' => $json,
			'search' => $search,
			'uniprotaddr' => $this->global['api']['uniprotaddr'],
			'pdbaddr' => $this->global['api']['pdbaddr'],
			'pdbjs' => $this->global['api']['pdbjs'],
			'id' => $args['id']
		];  

		$this->view->render($response, 'pdbmeta.html', $vars);

	}

	// get metadata for Monomer
	public function getMonomer($request, $response, $args) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, sprintf($this->global['api']['liginfo'], $args['id']));
		$json = curl_exec($ch);
		curl_close($ch);

		$json = json_decode($json);

		$json->formula = preg_replace("/([A-Za-z])([0-9]{1,})/", "$1<sub>$2</sub>", $json->formula);

		//if($args['search'] == 'search') { 

			$referer = implode("", $request->getHeader('HTTP_REFERER'));

			if((strrpos($referer, "/step1/search")) || (strrpos($referer, "/step1/similar"))) {
				$pdbmodel = $json->pdbx_model_coordinates_db_code;
			} else {
				$p = explode("/", $referer);
				$pdbmodel = $p[sizeof($p) - 1];
			}
			$search = true;
		/*}	else {
			$pdbmodel = "";
			$search = false;
			}*/


		$vars = [
			'page' => [
				'title' => 'Monomer Structure Metadata - '.$this->global['longProjectName'],
				'description' => 'Metadata relative to Monomer '.$args['id'],
				'basename' => 'monmeta'
			],
			'json' => $json,
			'search' => $search,
			'pdbmodel' => $pdbmodel,
			'ligjs' => $this->global['api']['ligjs'],
			'id' => $args['id']
		];  

		$this->view->render($response, 'hetmeta.html', $vars);

	}


	// get metadata for ChEMBL
	public function getChEMBL($request, $response, $args) {

		$rest = sprintf($this->global['api']['chemblmol'], $args['id']);

		$json = $this->utils->downloadXML($rest);

		if(!isset($json->error_message))
			$json->molecule_properties->full_molformula = preg_replace("/([A-Za-z])([0-9]{1,})/", "$1<sub>$2</sub>", $json->molecule_properties->full_molformula);

		$vars = [
			'page' => [
				'title' => 'ChEMBL Structure Metadata - '.$this->global['longProjectName'],
				'description' => 'Metadata relative to ChEMBL '.$args['id'],
				'basename' => 'chemblmeta'
			],
			'json' => $json,
			'id' => $args['id']
		];  

		$this->view->render($response, 'chemblmeta.html', $vars);

	}


	public function getMetaInfo($request, $response, $args) {

		$projectID = $args['id'];
		$projectMeta = $this->meta->getProjectMeta($projectID);
		
		echo '
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Value</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Number of MD replicas in Gromacs REMD</td>
						<td>'.$projectMeta['reps'].'</td>
					</tr>
					<tr>
						<td>Number of processors per MD replica</td>
						<td>'.$projectMeta['procs'].'</td>
					</tr>
					<tr>
						<td>Lenght of the MD simulations in REMD (ns)</td>
						<td>'.$projectMeta['len'].'</td>
					</tr>
					<tr>
						<td>Number of steps between REMD exchanges</td>
						<td>'.$projectMeta['replex'].'</td>
					</tr>
					<tr>
						<td>Initial (low) temperature in REMD (K)</td>
						<td>'.$projectMeta['tlow'].'</td>
					</tr>
					<tr>
						<td>Final (high) temperature in REMD (K)</td>
						<td>'.$projectMeta['thigh'].'</td>
					</tr>
					<tr>
						<td>Box size in REMD simulations (nm)</td>
						<td>'.$projectMeta['boxsize'].'</td>
					</tr>
					<tr>
						<td>Box type in REMD simulations (nm)</td>
						<td>'.$projectMeta['boxtype'].'</td>
					</tr>
					<tr>
						<td>REMD ensemble</td>
						<td>'.$projectMeta['ensemble'].'</td>
					</tr>
					<tr>
						<td>Clustering RMSd (nm)</td>
						<td>'.$projectMeta['cluster'].'</td>
					</tr>
					<tr>
						<td>Clustering Method</td>
						<td>'.$projectMeta['clusterMethod'].'</td>
					</tr>
					<tr>
						<td>Number of maximum clusters to analyse quantically (with Gaussian)</td>
						<td>'.$projectMeta['maxClusters'].'</td>
					</tr>
				</tbody>
			</table>';
			
			if($projectMeta['description'] != '') {
			echo '
				<table class="table table-striped">
				<thead>
					<tr>
						<th>Project description</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>'.$projectMeta['description'].'</td>				
					</tr>
				</tbody>
				</table>
			';
			}
	
	}

	public function getMolProperties($request, $response, $args) {

		$rest = sprintf($this->global['api']['chembl'], urlencode($args['id']), '100');

		$json = $this->utils->downloadXML($rest);

		$json = $json->molecules->molecule->molecule_properties;

		$json = json_decode(json_encode($json), True);

		if(isset($json)) {

		foreach($json as $k => $v) {

			if(is_Array($v)) $json[$k] = "";

		}

		echo '
			<div class="row">
				<div class="col-md-6">
					<table class="table table-striped table-bordered table-meta">
						<tbody>
							<tr>
								<td class="col-md-6"><strong>Acd logd</strong></td>
								<td class="col-md-6">'.$json['acd_logd'].'</td>
							</tr>
							<tr>
								<td><strong>Acd logp</strong></td>
								<td>'.$json['acd_logp'].'</td>
							</tr>
							<tr>
								<td><strong>Acd most apka</strong></td>
								<td>'.$json['acd_most_apka'].'</td>
							</tr>
							<tr>
								<td><strong>Alogp</strong></td>
								<td>'.$json['alogp'].'</td>
							</tr>
							<tr>
								<td><strong>Aromatic rings</strong></td>
								<td>'.$json['aromatic_rings'].'</td>
							</tr>
							<tr>
								<td><strong>HBA</strong></td>
								<td>'.$json['hba'].'</td>
							</tr>
							<tr>
								<td><strong>HBA Lipinski</strong></td>
								<td>'.$json['hhba_lipinski'].'</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="col-md-6">
					<table class="table table-striped table-bordered table-meta">
						<tbody>
							<tr>
								<td class="col-md-6"><strong>HBD</strong></td>
								<td class="col-md-6">'.$json['hbd'].'</td>
							</tr>
							<tr>
								<td><strong>HBD Lipinski</strong></td>
								<td>'.$json['hbd_lipinski'].'</td>
							</tr>
							<tr>
								<td><strong>Heavy atoms</strong></td>
								<td>'.$json['heavy_atoms'].'</td>
							</tr>
							<tr>
								<td><strong>Molecular species</strong></td>
								<td>'.$json['molecular_species'].'</td>
							</tr>
							<tr>
								<td><strong>PSA</strong></td>
								<td>'.$json['psa'].'</td>
							</tr>
							<tr>
								<td><strong>QED weighted</strong></td>
								<td>'.$json['pqed_weighted'].'</td>
							</tr>
							<tr>
								<td><strong>RO3 pass</strong></td>
								<td>'.$json['ro3_pass'].'</td>
							</tr>
							<tr>
								<td><strong>RTB</strong></td>
								<td>'.$json['rtb'].'</td>
							</tr>
						</tbody>
					</table>
				</div>
				</div>';

		}else{

			echo "No molecule properties for this ligand.";

		}

	}

}

