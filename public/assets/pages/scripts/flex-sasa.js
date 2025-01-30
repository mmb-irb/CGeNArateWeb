var baseURL = $("#base-url").val();
var projectID = $("#projectID").val();
var strType = $("#strType").val();
var resolution = $("#resolution").val();
var traj;
var stage;
var currBP;
var currentFrame = 1;

function frameStep(dir) {

	goToFrame(currentFrame + dir);

}

function goToFrame(f) {

	if((f >= 0) && (f < trajlength)) {
		currentFrame = f;
		$("#label-frame").html(f + 1);
		$("input#sliderframe[type=range]").val(f + 1);
		traj.setFrame(f);
		loadSASAPlot('sasa', 'SASA for snapshot ', ['Sequence base', 'SASA (Å²)'], (f + 1));
	}
}

function load3D() {

	$("#loading-viewport").show();

	$('#viewport').html('');

	$.ajax({
			
		type: "POST",
		url: baseURL + "path/for/" + projectID,
		success: function(response) {

			var obj = JSON.parse(response);

			stage = new NGL.Stage( "viewport", { backgroundColor:"#c7ced1", tooltip:true } );
			stage.removeAllComponents();

			if(strType == 'traj') var path = obj.path + obj.trajPath + obj.trajPDB;
			else var path = obj.path + obj.strPath + obj.strPDB;

			stage.loadFile(path, { defaultRepresentation: false } )
			.then( function( o ){

				o.setSelection('/2');

				if(strType == 'traj') {
					var framesPromise = NGL.autoLoad( obj.path + obj.trajPath + obj.trajDCD, { ext: "dcd" })
					.then( function( frames ){
						traj = o.addTrajectory( frames ).trajectory;
						
						trajlength = traj.frames.length;

						$("#loading-viewport").hide();
						o.setSelection('/0');
						o.autoView();

						$('#sliderframe').attr('max', trajlength);
		
						$('.frames-btn').removeClass('disabled');
						$("input#sliderframe[type=range]").prop('disabled', false);

						goToFrame(0);

					} );

				} else {
					
					$("#loading-viewport").hide();
					o.setSelection('/0');
					o.autoView();
					loadSASAPlot('sasa', 'SASA for snapshot ', ['Sequence base', 'SASA (Å²)'], 1);
	
				}

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

				} else {

					if($("#tool").val() == 3) {
						o.addRepresentation( "cartoon", {
							sele: "not(water or ion or :A or :B)", scale: 1.5, aspectRatio: 1.5, color: "sstruc"
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

				o.autoView();

			} );

			/*function handleResize(){ if(typeof stage != 'undefined') stage.handleResize(); }
			window.addEventListener( "resize", handleResize, false );*/

			window.addEventListener( "resize", function( event ){
					stage.handleResize();
			}, false );

		}

	});

	

}

function drawGraph(figure, name, maintit, titles, snapshot) {

	var trace1 = {
		x: figure.x,
		y: figure.y1,
		name: "SASA",
		marker: {
			color: "#525e64"
		},
		type: 'lines'
	};

	var trace2 = {
		x: figure.x,
		y: figure.y2,
		name: "Reference SASA",
		marker: {
			color: "#f3c200"
		},
		type: 'lines'
	};

	var trace3 = {
		x: figure.x,
		y: figure.y3,
		name: "Corrected SASA",
		marker: {
			color: "#cc6600"
		},
		type: 'lines'
	};

	var data = [trace1, trace2, trace3];

	var lineEndOfStrand = {
		yref: 'paper',
		xref: 'x',
		type: 'line',
		x0: (figure.x.length/2) - 1,
		y0: 0,
		x1: (figure.x.length/2) - 1,
		y1: 1,
		line: {		
			color: '#0c0',
			width: 3,
			dash: 'dash'
		}
	};

	var arrShapes = [lineEndOfStrand];
	var arrAnnotations = [];

	$.each(figure.proteins, function (k, item) {

		var block = {
				type: 'rect',
				xref: 'x',
				yref: 'paper',
				x0: item.pos1 - 1,
				y0: 0,
				x1: item.pos2 - 1,
				y1: 1,
				fillcolor: '#807270',
				opacity: 0.2,
				line: {
						width: 0
				}
			};

		arrShapes.push(block);

		var annotation = {
      x: item.pos1,
      y: 1,
      xref: 'x',
      yref: 'paper',
      text: item.id,
      showarrow: true,
			font: { color: '#807270' },
			arrowcolor: '#807270',
      arrowhead: 2,
      ax: 0,
      ay: -30
    };

		arrAnnotations.push(annotation);

	});

	var layout = {
		legend: {orientation: "h", x:0, y:-0.3},
		showlegend: true,
		title: maintit + snapshot,
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
		shapes: arrShapes,
		annotations: arrAnnotations
	};

	Plotly.newPlot('sasaPlotDiv', data, layout).then(function() {

		var o = stage.compList[0];

		o.removeAllAnnotations();

		var aProt = figure.proteins.splice(0, Math.ceil(figure.proteins.length / 2));
		var chainText = [];

		$.each(aProt, function(k, v) {
			chainText[v.chain] = v.id;
		});

		var ap = o.structure.getAtomProxy();
		o.structure.eachChain(function (cp) {
			if((cp.chainname != "A") &&(cp.chainname != "B")) {
				ap.index = cp.atomOffset + Math.floor(cp.atomCount / 2);
				o.addAnnotation(ap.positionToVector3(), chainText[ cp.chainname ]);
			}
		});

		o.autoView();

	});

	sasaPlotDiv.on('plotly_hover', function(data){
		
		var coord = data.points[0].x.split("-");
	
    currBP =	stage.compList[0].addRepresentation( "spacefill", {
			sele: coord[1], scale: 2, radius:2, color: '#fff', opacity:0.5
		} );

	}).on('plotly_unhover', function(data){

		stage.compList[0].removeRepresentation(currBP);	

	});

}

function loadSASAPlot(name, maintit, titles, snapshot) {

	$("#loading-sasa").show();

	Plotly.d3.json(baseURL + 'backoutput/flex/' + projectID + '/' + strType+ '/sasa/sasa/' + snapshot , function(figure) {

	if(!figure.error) {

		drawGraph(figure, name, maintit, titles, snapshot);

	}else{
		$("#loading-sasa").html("Error loading plot data. Please, try later.");
	}

	});

}

window.addEventListener('resize', function(){ 
	sasaPlotDiv.layout.width = $("#sasaPlotDiv").width();
	Plotly.redraw(sasaPlotDiv);
}, true);

$(document).ready(function() {

	$('html,body').animate({ scrollTop: $("#menu-flex").offset().top}, 10);

	load3D();

	$("input#sliderframe[type=range]").on('input', function () {
		goToFrame(parseInt($(this).val() - 1));
	});

});
