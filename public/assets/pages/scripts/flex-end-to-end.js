var baseURL = $("#base-url").val();
var projectID = $("#projectID").val();
var strType = $("#strType").val();
var resolution = $("#resolution").val();
var traj;
var arrayDistancesFrames;
var arrayDistancesSort;
var slider;

function goToFrame(f) {
	traj.setFrame(f);
}

function load3D(objGlobal) {

	arrayDistancesFrames = objGlobal.py;
	arrayDistancesSort = objGlobal.adistsort;

	$("#loading-viewport").show();

	$('#viewport').html('');

	$.ajax({
			
		type: "POST",
		url: baseURL + "path/for/" + projectID,
		success: function(response) {

			var obj = JSON.parse(response);

			stage = new NGL.Stage( "viewport", { backgroundColor:"#c7ced1", tooltip:true } );
			stage.removeAllComponents();

			stage.loadFile(obj.path + obj.trajPath + obj.trajPDB, { defaultRepresentation: false } )
			.then( function( o ){

				o.setSelection('/2');

				var framesPromise = NGL.autoLoad( obj.path + obj.trajPath + obj.trajDCD, { ext: "dcd" })
				.then( function( frames ){
					traj = o.addTrajectory( frames ).trajectory;
					
					trajlength = traj.frames.length;

					$("#loading-viewport").hide();
					o.setSelection('/0');
					o.autoView();

					var frame = arrayDistancesFrames.indexOf(arrayDistancesSort[0]);
					goToFrame(frame);

					$("#range").ionRangeSlider({
						min: 0,
						max: (trajlength - 1),
						grid: true,	
						values: arrayDistancesSort,
						postfix: "Å",			
						prettify_enabled: false,
						onChange: function (data) {
							var frame = arrayDistancesFrames.indexOf(data.from_value);
							goToFrame(frame);
							var maxv = Math.max(...arrayDistancesFrames);
							var minv = Math.min(...arrayDistancesFrames);
							drawGraph(objGlobal, 'distances', 'Distance between ', ['Snapshot index', 'Distance (Ångströms)'], 0, (frame + 1), minv, (frame + 1), maxv);
						
						}
					});

					slider = $("#range").data("ionRangeSlider");

					$("#loading-range").hide();
					$("#range").show();

				} );

				if(resolution == 1) {
			
					if($("#tool").val() == 3) {
						o.addRepresentation( "cartoon", {
							sele: "not(water or ion or :A or :B)", scale: 1.5, aspectRatio: 10.5, color: "sstruc"
						} );
						o.addRepresentation( "licorice", {
							sele: ":A or :B", scale: 1.5, aspectRatio: 1.5
						} );
					} else {
						o.addRepresentation( "licorice", {
							sele: "not(water or ion or P)", scale: 1.5, aspectRatio: 1.5
						} );
					}

				} else {

					if($("#tool").val() == 3) {
						o.addRepresentation( "cartoon", {
							sele: "not(water or ion or :A or :B)", scale: 1.5, aspectRatio: 10.5, color: "sstruc"
						} );
						o.addRepresentation( "ball+stick", {
							sele: ":A or :B", radius: .5
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

				//var resPair = [ [ [objGlobal.rp1], [objGlobal.rp2] ] ];
				var resPair = [[objGlobal.rp1 + ".C1'", objGlobal.rp2 + ".C1'"]];
				o.addRepresentation( "distance", { atomPair: resPair, labelSize:10, labelUnit: 'angstrom', color: "white" } );

				o.autoView();
			} );

			function handleResize(){ if(typeof stage != 'undefined') stage.handleResize(); }
			window.addEventListener( "resize", handleResize, false );

		}

	});

	

}

function drawGraph(figure, name, maintit, titles, type, x0, y0, x1, y1) {

	var trace = {
		x: figure.px,
		y: figure.py,
		name: name,
		marker: {
			color: "#cc6600"
		},
		type: 'lines'
	};

	var data = [trace];

	var layout = {
		showlegend: false,
		title: maintit + figure.rp1 + '-' + figure.rp2 + ' bp',
		autosize: true,
		xaxis: {
			title: titles[0],
			showline: true,
			showgrid: false,
			zeroline: false
		},
		yaxis: {
			title: titles[1],
			showline: true,
			showgrid: false,
			zeroline: false
		},
		shapes: [
			{
				xref: 'x',
        yref: 'paper',
				type: 'line',
				x0: x0,
				y0: 0,
				x1: x1,
				y1: 1,
				line: {
					color: '#525e64',
					width: 1,
					dash: "dash"
				}
			}
		]
	};

	if(type == 1) {

		Plotly.newPlot('endToEndPlotDiv', data, layout).then(function(){
			$("#loading-plot").hide();
		
			load3D(figure);

		});

	}else{

		Plotly.newPlot('endToEndPlotDiv', data, layout);

	}

	endToEndPlotDiv.on('plotly_click', function(data){
		loadDataFromPlot(data.points[0].x, data.points[0].y, figure);
		
	});

}

function loadDataFromPlot(x, y, figure) {

	var frame = arrayDistancesFrames.indexOf(y);
	var maxv = Math.max(...arrayDistancesFrames);
	var minv = Math.min(...arrayDistancesFrames);

	drawGraph(figure, 'distances', 'Distance between ', ['Snapshot index', 'Distance (Ångströms)'], 0, (frame + 1), minv, (frame + 1), maxv);

	slider.update({
		from: arrayDistancesSort.indexOf(y),
	});

	goToFrame(arrayDistancesFrames.indexOf(y));

}

function loadEndToEndPlot(name, maintit, titles) {

	$("#loading-plot").show();

	Plotly.d3.json(baseURL + 'backoutput/flex/' + projectID + '/' + strType+ '/end-to-end/end-to-end' , function(figure) {
	if(!figure.error) {

		arrayDistancesFrames = figure.py;
		arrayDistancesSort = figure.adistsort;
		var frameinit = (arrayDistancesFrames.indexOf(arrayDistancesSort[0]) + 1);

		var maxv = Math.max(...arrayDistancesFrames);
		var minv = Math.min(...arrayDistancesFrames);

		drawGraph(figure, name, maintit, titles, 1, frameinit, minv, frameinit, maxv);

		var persistenceLength = figure.pl;
		if ($("#pl").length) {
			$("#pl").text(persistenceLength);
		}

	}else{
		$("#loading-plot").html("Error loading plot data. Please, try later.");
	}

	});

}

window.addEventListener('resize', function(){ 
	endToEndPlotDiv.layout.width = $("#endToEndPlotDiv").width();
	Plotly.redraw(endToEndPlotDiv);
}, true);

$(document).ready(function() {

	$('html,body').animate({ scrollTop: $("#menu-flex").offset().top}, 10);

	loadEndToEndPlot('distances', 'Distance between ', ['Snapshot index', 'Distance (Ångströms)']);

	var viewFullScreen = document.getElementById("view-fullscreen");
	if (viewFullScreen) {
    viewFullScreen.addEventListener("click", function () {
			var docElm = document.getElementById("viewport");
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

			stage.toggleFullscreen(document.body.viewport);
    });
	}

});
