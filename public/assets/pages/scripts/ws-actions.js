
var baseURL = $('#base-url').val();	
var userID = $('#user-id').val();

var fileID = '';
var option = '';

var checkJobsInterval = 0;

function deleteFile(file, name){
  $('#modalDelete .modal-body').html('Are you sure you want to delete the file <strong>' + name + '</strong> ?');
  $('#modalDelete').modal({ show: 'true' });
  fileID = file;
  option = 'deleteFile';
}

function deleteFolder(folder, name){
  $('#modalDelete .modal-body').html('Are you sure you want to delete the folder <strong>' + name + '</strong> and <strong>ALL</strong> its content?');
  $('#modalDelete').modal({ show: 'true' });
  fileID = folder;
  option = 'deleteDir';
}	

function cancelJob(folder, name){
  $('#modalDelete .modal-body').html('Are you sure you want to cancel the currently running job <strong>' + name + '</strong> and remove all the data related to it?');
  $('#modalDelete').modal({ show: 'true' });
  fileID = folder;
  option = 'deleteDir';
}	

function deleteJob(folder) {
	
	$.ajax({
				
		type: "GET",
		url: baseURL + "delete/folder/" + folder,
		success: function(data) {

			location.href = baseURL + "workspace";
		
		}

	});

}

function openNode(nodeID, parentID) {

	Cookies.set('folder', nodeID);

	$('#tree').jstree('select_node', nodeID);

	$('#tree').jstree('deselect_node', parentID);

}

function checkRunningJobs() {

	$.ajax({
				
		type: "GET",
		url: baseURL + "/check/jobs",
		success: function(data) {

			var d = JSON.parse(data);

			if(!d.runningJobs) {

				clearInterval(checkJobsInterval);

				if(d.jobFinished) location.href = baseURL + "workspace";

			}

		}

	});

}

function showMeta(project, name) {

	$.ajax({

		type: "GET",
		url: baseURL + "get/meta/" + project,
		success: function(data) {

			$('#modalMeta .modal-title').html('Metadata for ' + name);
			$('#modalMeta .modal-body').html(data);
  		$('#modalMeta').modal({ show: 'true' });
		
		}

	});

}	


$(document).ready(function() {

	// delete files and folders, cancel jobs, etc
	$('#modalDelete').find('.modal-footer .btn-modal-del').on('click', function(){

		if(option == 'deleteFile') {

			$('#dynamic-table').html('');
			$('#dynamic-table').addClass('dtloading');	

			$.ajax({
				
				type: "GET",
				url: baseURL + "delete/file/" + fileID,
				success: function(data) {

					location.href = baseURL + "workspace";
				
				}

			});

		}

		if(option == 'deleteDir') {

			$('#dynamic-table').html('');
			$('#dynamic-table').addClass('dtloading');	

			$.ajax({
				
				type: "GET",
				url: baseURL + "delete/folder/" + fileID,
				success: function(data) {

					location.href = baseURL + "workspace";
				
				}

			});

		}
		
	});

	// check finished jobs
	checkJobsInterval = setInterval( checkRunningJobs, wsTimeOut);

});


