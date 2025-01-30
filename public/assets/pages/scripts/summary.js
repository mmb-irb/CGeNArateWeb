var baseURL = $("#base-url").val();
var projectID = $("#projectID").val();
var resolution = $("#resolution").val();

var ComponentsClipboard = function() {

    return {
        //main function to initiate the module
        init: function() {
        	var paste_text;

        	$('.mt-clipboard').each(function(){
        		var clipboard = new Clipboard(this);	

        		clipboard.on('success', function(e) {
				    paste_text = e.text;
				    console.log(paste_text);
				});
        	});

        	$('.mt-clipboard').click(function(){
    			if($(this).data('clipboard-paste') == true){
    				if(paste_text){
        				var paste_target = $(this).data('paste-target');
        				$(paste_target).val(paste_text);
        				$(paste_target).html(paste_text);
        			} else {
        				alert('No text was copied or cut.');
        			}
        		} 
    		});
        }
    }

}();

$(document).ready(function() {

	if($('#status').val() == 5) {

		$("input#sliderframe[type=range]").on('input', function () {

			trajectoryPlayer.pause();
			$("#pause-btn").hide();
			$("#play-btn").show();
			playFlag = 0;

			traj.setFrame(parseInt($(this).val()));
		});

		$.ajax({
			
			type: "POST",
			url: baseURL + "path/for/" + projectID,
			success: function(response) {

				var obj = JSON.parse(response);

				if(($('#trajectory').val() == 0) || ($('#trajectory').val() == 2)) {

					if(obj.strPDB != "error") drawStructure(obj.path, obj.strPath, obj.strPDB);
					else $("#loading-viewport1").html("<span style='margin-left:-20px;'>ERROR loading structure</span>");


					//makeplot(path, "str01", "5 bp segments", "10 bp segments", "spline", "Bending (in deg)", "Density");
					//makeplot(path, "str02", "5 bp segments", "10 bp segments", "linear", "# bp", "Bending (in deg)");

				}

				if(($('#trajectory').val() == 1) || ($('#trajectory').val() == 2)) {

					if((obj.trajPDB != "error") && (obj.trajDCD != "error")) drawTrajectory(obj.path, obj.trajPath, obj.trajPDB, obj.trajDCD);
					else $("#loading-viewport2").html("<span style='margin-left:-20px;'>ERROR loading trajectory</span>");

					//makeplot(path, "traj01", "5 bp segments", "10 bp segments", "spline", "Bending (in deg)", "Density");
					//makeplot(path, "traj02", "5 bp segments", "10 bp segments", "linear", "# bp", "Bending (in deg)");

				}

			}

		});

		var viewFullScreen1 = document.getElementById("view-fullscreen1");
		if (viewFullScreen1) {
			viewFullScreen1.addEventListener("click", function () {
				var docElm = document.getElementById("viewport1");
				if (docElm.requestFullscreen) {
						docElm.requestFullscreen();
				}
				else if (docElm.msRequestFullscreen) {
						docElm.msRequestFullscreen();
				}
				else if (docElm.mozRequestFullScreen) {
						docElm.mozRequestFullScreen();
				}
				else if (docElm.webkitRequestFullScreen) {
						docElm.webkitRequestFullScreen();
				}

				stage1.toggleFullscreen(document.body.viewport1);
			});
		}

		var viewFullScreen2 = document.getElementById("view-fullscreen2");
		if (viewFullScreen2) {
			viewFullScreen2.addEventListener("click", function () {
				var docElm = document.getElementById("viewport2");
				if (docElm.requestFullscreen) {
						docElm.requestFullscreen();
				}
				else if (docElm.msRequestFullscreen) {
						docElm.msRequestFullscreen();
				}
				else if (docElm.mozRequestFullScreen) {
						docElm.mozRequestFullScreen();
				}
				else if (docElm.webkitRequestFullScreen) {
						docElm.webkitRequestFullScreen();
				}

				stage2.toggleFullscreen(document.body.viewport2);
			});
		}

	}else if($('#status').val() == 4) {

			ComponentsClipboard.init();

			// first time we check status
			$.ajax({
		
				type: "GET",
				url: baseURL + "progress/for/" + projectID,
				success: function(output) {

					$("#progress-job").html(output);

				}

			});

			setInterval(function() { getStatus(projectID) }, 1000);

	} else {

	
	}

});

/**************************************/
/*      STRUCTURE VISUALIZATION       */
/**************************************/

function drawStructure(path, strPath, strPDB) {

	$('#viewport1').html('');
	stage1 = new NGL.Stage( "viewport1", { backgroundColor:"#c7ced1", tooltip:false } );
	stage1.removeAllComponents();

	stage1.loadFile( path + strPath + strPDB, { defaultRepresentation: false } )
	.then( function( o ){

		if(resolution == 1) {

			if($("#tool").val() == 3) {
				o.addRepresentation( "cartoon", {
					sele: "not(water or ion or :A or :B)", scale: 1.5, aspectRatio: 1.5, color: "sstruc"
				} );
				o.addRepresentation( "licorice", {
					sele: ":A or :B", scale: 1.5, aspectRatio: 1.5
				} );
			} else {
				o.addRepresentation( "licorice", {
					sele: "not(water or ion or P)", scale: 1.5, aspectRatio: 1.5
				} );
			}

		}else{

			if($("#tool").val() == 3) {
				o.addRepresentation( "cartoon", {
					sele: "not(water or ion or :A or :B)", scale: 1.5, aspectRatio: 1.5, color: "sstruc"
				} );
				o.addRepresentation( "ball+stick", {
					sele: ":A or :B"
				} );
				o.addRepresentation( "spacefill", {
					sele: ":A.P1 or :B.P1", scale: 1, radius:1, color: "element"
				} );
			} else {
				o.addRepresentation( "ball+stick", {
					sele: "not .P1", color: "element", radius: .5
				} );

				o.addRepresentation( "spacefill", {
					sele: ".P1", scale: 1, radius:1, color: "element"
				} );
			}

		}

		stage1.autoView();
		$("#loading-viewport1").hide();	
	} );

	function handleResize1(){ if(typeof stage1 != 'undefined') stage1.handleResize(); }
	window.addEventListener( "resize", handleResize1, false );

}

// View trajectories
var trajectoryPlayer;
var traj;
var currentFrame = 0;
var step = 1;

function updateCurrentFrame(f) {
	currentFrame = parseInt(f);
	$("#label-frame").html(f + 1);
//$("input[type=range]")
	$("input#sliderframe[type=range]").val(f);
	//if(f == (totalFrames - step)) traj.setFrame(0);

}

var playFlag = 1;

function frameToggle() {

	if(!playFlag) {
		trajectoryPlayer.play();
		$("#pause-btn").show();
		$("#play-btn").hide();
		playFlag = 1;
	} else {
		trajectoryPlayer.pause();
		$("#pause-btn").hide();
		$("#play-btn").show();
		playFlag = 0;	
	}

}

function frameStep(dir) {

	trajectoryPlayer.pause();
	$("#pause-btn").hide();
	$("#play-btn").show();
	playFlag = 0;

	console.log(currentFrame + (dir * step));

	if(((currentFrame + (dir * step)) >= 0) && ((currentFrame + (dir * step)) <= (totalFrames - 1))) traj.setFrame(currentFrame + (dir * step));
//	else traj.setFrame(9999);

}


function drawTrajectory(path, trajPath, trajPDB, trajDCD) {

	$('#viewport2').html('');
	stage2 = new NGL.Stage( "viewport2", { backgroundColor:"#c7ced1", tooltip:false } );
	stage2.removeAllComponents();

	stage2.loadFile(path + trajPath + trajPDB, { defaultRepresentation: false } )
	.then( function( o ){
		$("#loading-trajectories").show();
		var framesPromise = NGL.autoLoad( path + trajPath + trajDCD, { ext: "dcd" })
		.then( function( frames ){
			traj = o.addTrajectory( frames ).trajectory;
			totalFrames = traj.frames.length;
			var player = new NGL.TrajectoryPlayer( traj, {
					step: step,
					timeout: 80,
					start: 0,
					end: totalFrames,
					//interpolateType: "linear",
					mode: "loop"
			} );
			player.end = traj.frames.length;
			player.play();
			trajectoryPlayer = traj.player;

			traj.signals.frameChanged.add((a) => {
				updateCurrentFrame(a);
			});

			$('.frames-btn').removeClass('disabled');
			$("input#sliderframe[type=range]").prop('disabled', false);

			$("#loading-trajectories").hide();
		} );

		if(resolution == 1) {

			if($("#tool").val() == 3) {
				o.addRepresentation( "cartoon", {
					sele: "not(water or ion or :A or :B)", scale: 1.5, aspectRatio: 1.5, color: "sstruc"
				} );
				o.addRepresentation( "licorice", {
					sele: ":A or :B", scale: 1.5, aspectRatio: 1.5
				} );
			} else {
				o.addRepresentation( "licorice", {
					sele: "not(water or ion)", scale: 1.5, aspectRatio: 1.5
				} );
			}

		}else{

			if($("#tool").val() == 3) {
				o.addRepresentation( "cartoon", {
					sele: "not(water or ion or :A or :B)", scale: 1.5, aspectRatio: 1.5, color: "sstruc"
				} );
				o.addRepresentation( "ball+stick", {
					sele: ":A or :B"
				} );
				o.addRepresentation( "spacefill", {
					sele: ":A.P1 or :B.P1", scale: 1, radius:1, color: "element"
				} );
			} else {
				o.addRepresentation( "ball+stick", {
					sele: "not .P1", color: "element", radius: .5
				} );

				o.addRepresentation( "spacefill", {
					sele: ".P1", scale: 1, radius:1, color: "element"
				} );
			}

		}

		$("#loading-viewport2").hide();
		o.autoView();
	} );

	function handleResize2(){ if(typeof stage2 != 'undefined') stage2.handleResize(); }
	window.addEventListener( "resize", handleResize2, false );

	

}

/**************************************/
/*          STATUS CHECKING           */
/**************************************/

function getStatus(projectID) {

	$.ajax({
		
		type: "POST",
		url: baseURL + "get/status/" + projectID,
		success: function(output) {

			out = $.parseJSON(output);

			//**********************
			//if(out.status == 4) {
			if(out.status == 5) {
			//************************

				location.href = baseURL + "output/summary/" + projectID;

			} else {

				$.ajax({
		
					type: "GET",
					url: baseURL + "progress/for/" + projectID,
					success: function(output) {

						$("#progress-job").html(output);

					}

				});

			}

		}

	});

}


