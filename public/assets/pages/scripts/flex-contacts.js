var baseURL = $("#base-url").val();
var projectID = $("#projectID").val();
var strType = $("#strType").val();
var resolution = $("#resolution").val();
var contactType = "NUC-NUC";
var genericContType = "NUC-NUC";
var currSurface;
var currSurfaceArr = [];
var protOrder = [];

var axisLabels = ['Sequence', 'Sequence'];
/*var axis = {
	'nucnuc' : ['Sequence', 'Sequence']
};*/

// definir array de dades amb title i axis segons contactType

function loadTypeOfContact(typeOfContact) {

	genericContType = typeOfContact;

	$('.menu-contacts').each( function() {
		$(this).removeClass('not-active');
		$(this).addClass('btn-outline');
		$(this).prop('disabled', false);
	});

	$('#bt-' + typeOfContact).addClass('not-active');
	$('#bt-' + typeOfContact).removeClass('btn-outline');
	$('#bt-' + typeOfContact).prop('disabled', true);

	$('.form-contacts').each( function() {
		$(this).addClass('display-hide');
	});

	$('#form-' + typeOfContact).removeClass('display-hide');

	$('.nav-tabs a[href=\'#mean\']').click();

	if(typeOfContact == 'NUC-NUC') {
		$("#3d-view").hide();
		$("#dist-prots").hide(); 
		$("#heatmap").show();
		axisLabels = ['Sequence', 'Sequence'];
		loadHeatMap('mean', 'Distances (Ångströms)', axisLabels, 'NUC-NUC');
		$("#heatmap-type").html("Nucleotide - Nucleotide");
	} else if(typeOfContact == 'PROT-NUC') {
		$("#heatmap").hide(); 
		$("#dist-prots").hide(); 
		$("#3d-view").show();
		$('#submit-prot-nuc').prop('disabled', true);
		$('input[type=radio][name=proteinIDradio]').prop('checked', false);
		$("#heatmap-type").html("Protein - Nucleotide");
	} else if(typeOfContact == 'PROT-PROT') {
		$("#heatmap").hide(); 
		$("#dist-prots").hide(); 
		$("#3d-view").show();
		$('#submit-prot-prot').prop('disabled', true);
		$('input[type=checkbox][name=proteinIDcheckbox]').prop('checked', false);
		$("#heatmap-type").html("Protein - Protein");
	}else if(typeOfContact == 'PROT-PROT2') {
		$("#heatmap").hide(); 
		$("#dist-prots").hide(); 
		$("#3d-view").show();
		$('#submit-prot-prot2').prop('disabled', true);
		$('input[type=checkbox][name=proteinIDcheckbox2]').prop('checked', false);
		//$("#heatmap-type").html("Protein - Protein");
	}

	if((typeOfContact == 'PROT-NUC') || (typeOfContact == 'PROT-PROT') || (typeOfContact == 'PROT-PROT2')) {

		$.ajax({
				
				type: "POST",
				url: baseURL + "path/for/" + projectID,
				success: function(response) {

					var obj = JSON.parse(response);

					$('#viewport').html('');
					stage = new NGL.Stage( "viewport", { backgroundColor:"#c7ced1", tooltip:true } );
					stage.removeAllComponents();

					if(strType == 'traj') {
						var sPath = obj.trajPath;
						var sPDB = obj.trajPDB;
					}else{
						var sPath = obj.strPath;
						var sPDB = obj.strPDB;
					}

					stage.loadFile( obj.path + sPath + sPDB, { defaultRepresentation: false } )
					.then( function( o ){

						if(resolution == 1) {

							o.addRepresentation( "cartoon", {
								sele: "not(water or ion or :A or :B)", scale: 1.5, aspectRatio: 10.5, color: "sstruc"
							} );
							o.addRepresentation( "licorice", {
								sele: ":A or :B", scale: 1.5, aspectRatio: 1.5
							} );

						}else{

							o.addRepresentation( "cartoon", {
								sele: "not(water or ion or :A or :B)", scale: 1.5, aspectRatio: 10.5, color: "sstruc"
							} );
							o.addRepresentation( "ball+stick", {
								sele: ":A or :B", radius: .5
							} );
							o.addRepresentation( "spacefill", {
								sele: ":A.P1 or :B.P1", scale: 1, radius:1, color: "element"
							} );

						}

						stage.autoView();
						$("#loading-viewport").hide();
		
					} );

					function handleResize(){ if(typeof stage != 'undefined') stage.handleResize(); }
					window.addEventListener( "resize", handleResize, false );

				}

		});

	}

}

function arraysEqual(arr1, arr2) {
    if(arr1.length !== arr2.length)
        return false;
    for(var i = arr1.length; i--;) {
        if(arr1[i] !== arr2[i])
            return false;
    }

    return true;
}

function loadHeatMap(type, title, axis, contTyp) {

	if($("#meanPlotDiv").length) Plotly.purge(meanPlotDiv);
	if($("#minPlotDiv").length) Plotly.purge(minPlotDiv);
	if($("#maxPlotDiv").length) Plotly.purge(maxPlotDiv);
	if($("#stdevPlotDiv").length) Plotly.purge(stdevPlotDiv);

	$("#loading-" + type).show();
	contactType = contTyp;

	Plotly.d3.json(baseURL + 'backoutput/flex/' + projectID + '/' + strType + '/contacts/' + type + '/' + contTyp, function(figure) {
		if(!figure.error) {

			if(genericContType == "PROT-PROT") {

				var ct = contTyp.split("-");

				if(!arraysEqual(ct, figure.protOrder)) axis.reverse();

			}
	
		var data = [{
			x: figure.x,
			y:figure.y,
			z: figure.z,
			colorscale: [
				['0.0', '#cc6600'],
				['1.0', '#eeeeee']
			],
			type: 'heatmap',
			colorbar: {
				thickness:15
			}
		}];
	
		if(strType == 'traj') var tit = title + ': ' + type.toUpperCase();
		else var tit = title;

		var layout = {
			title: tit,
			autosize: true,
			xaxis: {
				ticks: "",
				title: axis[0]
			},
			yaxis: {
				ticks: "",
				ticksuffix: " ",
				title: axis[1]
			},
			/*shapes: [
			//line vertical
			{
				type: 'line',
				x0: 55.5,
				y0: -0.5,
				x1: 55.5,
				y1: 111.5,
				line: {
					color: '#000',
					width: 1
				}
			},
				//line horizontal
			{
				type: 'line',
				x0: -0.5,
				y0: 55.5,
				x1: 111.5,
				y1: 55.5,
				line: {
					color: '#000',
					width: 1
				}
			},
		]*/
		};

		Plotly.newPlot(type + 'PlotDiv', data, layout).then(function(){
			$("#loading-" + type).hide();
		});
	}else{
		$("#loading-" + type).html("Error loading plot data. Please, try later.");
	}

 });

}

window.addEventListener('resize', function(){ 
		if($("#meanPlotDiv").hasClass('js-plotly-plot')) {
			meanPlotDiv.layout.width = $("#meanPlotDiv").width();
			Plotly.redraw(meanPlotDiv);
		}
		if($("#minPlotDiv").hasClass('js-plotly-plot')) {
			minPlotDiv.layout.width = $("#minPlotDiv").width();
			Plotly.redraw(minPlotDiv);
		}
		if($("#maxPlotDiv").hasClass('js-plotly-plot')) {
			maxPlotDiv.layout.width = $("#maxPlotDiv").width();
			Plotly.redraw(maxPlotDiv);
		}
		if($("#stdevPlotDiv").hasClass('js-plotly-plot')) {
			stdevPlotDiv.layout.width = $("#stdevPlotDiv").width();
			Plotly.redraw(stdevPlotDiv);
		}
}, true);

function goToFrame(f) {
	traj.setFrame(f);
}

var traj;
var arrayDistancesFrames;
function load3D(prots/*objGlobal*/) {

	//arrayDistancesFrames = objGlobal.py;

	$("#loading-viewport2").show();

	$('#viewport2').html('');

	$.ajax({
			
		type: "POST",
		url: baseURL + "path/for/" + projectID,
		success: function(response) {

			var obj = JSON.parse(response);

			stage2 = new NGL.Stage( "viewport2", { backgroundColor:"#c7ced1", tooltip:true } );
			stage2.removeAllComponents();

			stage2.loadFile(obj.path + obj.trajPath + obj.trajPDB, { defaultRepresentation: false } )
			.then( function( o ){

				o.setSelection('/2');

				var framesPromise = NGL.autoLoad( obj.path + obj.trajPath + obj.trajDCD, { ext: "dcd" })
				.then( function( frames ){
					traj = o.addTrajectory( frames ).trajectory;
					
					trajlength = traj.frames.length;

					$("#loading-viewport2").hide();
					o.setSelection('/0');
					o.autoView();

					var frame = arrayDistancesFrames.indexOf(arrayDistancesSort[0]);
					$("#distance-label").html("Distance: <strong>" + arrayDistancesSort[0] + " Å</strong>");
					goToFrame(frame);

					$("#loading-range").hide();
					$("#range").show();

				} );

				if(resolution == 1) {

					o.addRepresentation( "cartoon", {
						sele: "not(water or ion or :A or :B)", scale: 1.5, aspectRatio: 10.5, color: "sstruc"
					} );
					o.addRepresentation( "licorice", {
						sele: ":A or :B", scale: 1.5, aspectRatio: 1.5
					} );

				}else{

					o.addRepresentation( "cartoon", {
						sele: "not(water or ion or :A or :B)", scale: 1.5, aspectRatio: 10.5, color: "sstruc"
					} );
					o.addRepresentation( "ball+stick", {
						sele: ":A or :B", radius: .5
					} );
					o.addRepresentation( "spacefill", {
						sele: ":A.P1 or :B.P1", scale: 1, radius:1, color: "element"
					} );

				}

				var resPair = [[":" + prots[0], ":" + prots[1]]];
				o.addRepresentation( "distance", { atomPair: resPair, labelSize:0, labelUnit: 'angstrom', color: "white" } );

				o.autoView();
			} );

			function handleResize(){ if(typeof stage2 != 'undefined') stage2.handleResize(); }
			window.addEventListener( "resize", handleResize, false );

		}

	});

}

$(document).ready(function() {

	$('html,body').animate({ scrollTop: $("#menu-flex").offset().top}, 10);

	loadTypeOfContact('NUC-NUC');

	//loadHeatMap('mean', 'Distances (Ångströms)', ['Sequence', 'Sequence']);

	// TABS
	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
  	var target = $(e.target).attr("href") // activated tab
		loadHeatMap(target.substr(1), 'Distances (Ångströms)', axisLabels, contactType);
	});

	// PROT-NUC Proteins selection
	$('input[type=radio][name=proteinIDradio]').change(function() {

		var protein = ":" + $(this).val().substring(5,6);

		stage.compList[0].removeRepresentation(currSurface);

		$('#submit-prot-nuc').prop('disabled', false)

    currSurface = stage.compList[0].addRepresentation( "surface", {
			sele: protein, opacity:1, background: 'true', color: '#fff'
    } );

		stage.compList[0].autoView(protein, 1000);

  });

	$('#reset-view-prot-nuc').click(function(){
    stage.compList[0].removeRepresentation(currSurface);
		$('#submit-prot-nuc').prop('disabled', true);
    $('input[type=radio][name=proteinIDradio]').prop('checked', false);
    stage.animationControls.zoom();
		stage.autoView(1000);
  });

	$('#submit-prot-nuc').click(function(){
   	var prot = $('input[type=radio][name=proteinIDradio]:checked').val().substring(5,6);
   	var protName = $('input[type=radio][name=proteinIDradio]:checked').val().substring(0,4);
		$('.nav-tabs a[href=\'#mean\']').click();
		$("#heatmap").show();
		axisLabels = [protName + ' protein residues (C<sub>α</sub>)', 'Nucleotide sequence'];
		loadHeatMap('mean', 'Distances (Ångströms)', axisLabels, 'NUC-' + prot);
		var aTag = $("div[id='heatmap']");
		setTimeout(function(){ $('html,body').animate({scrollTop: aTag.offset().top},'slow'); }, 200);
  });

	// PROT-PROT Proteins selection
	$('input[type=checkbox][name=proteinIDcheckbox]').change(function() {

		var protChain = ":" + $(this).val().substring(5,6);

		if(this.checked) {

			var protein = protChain;

			var checked = $('input[type=checkbox][name=proteinIDcheckbox]:checked').length;

			if((checked >= 1) && (checked < 2)) {
				var csf = stage.compList[0].addRepresentation( "surface", {
					sele: protein, opacity:1, background: 'true', color: '#fff'
				} );
				currSurfaceArr.push(csf);
				$('#submit-prot-prot').prop('disabled', true);
			} else if((checked == 2)) {
				var csf = stage.compList[0].addRepresentation( "surface", {
					sele: protein, opacity:1, background: 'true', color: '#fff'
				} );
				currSurfaceArr.push(csf);
				$('#submit-prot-prot').prop('disabled', false);
			} else if(checked > 2) {
				$(this).prop('checked', false);
			}

		} else {

			$.each(currSurfaceArr, function(i, v) {

				if(v.repr.__sele == protChain) {
					stage.compList[0].removeRepresentation(v);
				}
				
			});

			var checked = $('input[type=checkbox][name=proteinIDcheckbox]:checked').length;
			if(checked == 0 || checked == 1) {
				$('#submit-prot-prot').prop('disabled', true);
			}

		}

  });

	$('#reset-view-prot-prot').click(function(){
		$.each(currSurfaceArr, function(i, v) {
			stage.compList[0].removeRepresentation(v);
		});
		$('#submit-prot-prot').prop('disabled', true);
    $('input[type=checkbox][name=proteinIDcheckbox]').prop('checked', false);
    stage.animationControls.zoom();
		stage.autoView(1000);
  });

	$('#submit-prot-prot').click(function(){

		var prot = [];
		var protName = [];

		//console.log(protOrder);
	
		$('input[type=checkbox][name=proteinIDcheckbox]').each(function () {
    	var sThisVal = (this.checked ? $(this).val() : "");
			if(sThisVal != "") { 
				prot.push(sThisVal.substring(5,6));
				protName.push(sThisVal.substring(0,4));
			}
		});	

		$('.nav-tabs a[href=\'#mean\']').click();
		$("#heatmap").show();
		axisLabels = [protName[1] + ' protein residues (C<sub>α</sub>)', protName[0] + ' protein residues (C<sub>α</sub>)'];
		loadHeatMap('mean', 'Distances (Ångströms)', axisLabels, prot[0] + '-' + prot[1]);
		var aTag = $("div[id='heatmap']");
		setTimeout(function(){ $('html,body').animate({scrollTop: aTag.offset().top},'slow'); }, 200);
   	
  })

	// PROT-PROT2 Proteins selection
	$('input[type=checkbox][name=proteinIDcheckbox2]').change(function() {

		var protChain = ":" + $(this).val().substring(5,6);

		if(this.checked) {

			var protein = protChain;

			var checked = $('input[type=checkbox][name=proteinIDcheckbox2]:checked').length;

			if((checked >= 1) && (checked < 2)) {
				var csf = stage.compList[0].addRepresentation( "surface", {
					sele: protein, opacity:1, background: 'true', color: '#fff'
				} );
				currSurfaceArr.push(csf);
				$('#submit-prot-prot2').prop('disabled', true);
			} else if((checked == 2)) {
				var csf = stage.compList[0].addRepresentation( "surface", {
					sele: protein, opacity:1, background: 'true', color: '#fff'
				} );
				currSurfaceArr.push(csf);
				$('#submit-prot-prot2').prop('disabled', false);
			} else if(checked > 2) {
				$(this).prop('checked', false);
			}

		} else {

			$.each(currSurfaceArr, function(i, v) {

				if(v.repr.__sele == protChain) {
					stage.compList[0].removeRepresentation(v);
				}
				
			});

			var checked = $('input[type=checkbox][name=proteinIDcheckbox2]:checked').length;
			if(checked == 0 || checked == 1) {
				$('#submit-prot-prot2').prop('disabled', true);
			}

		}

  });

	$('#reset-view-prot-prot2').click(function(){
		$.each(currSurfaceArr, function(i, v) {
			stage.compList[0].removeRepresentation(v);
		});
		$('#submit-prot-prot2').prop('disabled', true);
    $('input[type=checkbox][name=proteinIDcheckbox2]').prop('checked', false);
    stage.animationControls.zoom();
		stage.autoView(1000);
  });

	$('#submit-prot-prot2').click(function(){

		var prot = [];
		var protName = [];
	
		$('input[type=checkbox][name=proteinIDcheckbox2]').each(function () {
    	var sThisVal = (this.checked ? $(this).val() : "");
			if(sThisVal != "") { 
				prot.push(sThisVal.substring(5,6));
				protName.push(sThisVal.substring(0,4));
			}
		});	

		$("#label-dis-prot").html(protName[0] + " - " + protName[1] + " distances");
		$("#dist-prots").show(); 

		//load3D(prot);

		loadEndToEndPlot(prot, 'distances', 'Distance between ', ['Snapshot index', 'Distance (Ångströms)']);

		var aTag = $("div[id='dist-prots']");
		setTimeout(function(){ $('html,body').animate({scrollTop: aTag.offset().top},'slow'); }, 200);

		/*$('.nav-tabs a[href=\'#mean\']').click();
		$("#heatmap").show();
		axisLabels = [protName[1] + ' protein residues (C<sub>α</sub>)', protName[0] + ' protein residues (C<sub>α</sub>)'];
		loadHeatMap('mean', 'Distances (Ångströms)', axisLabels, prot[0] + '-' + prot[1]);
		var aTag = $("div[id='heatmap']");
		setTimeout(function(){ $('html,body').animate({scrollTop: aTag.offset().top},'slow'); }, 200);*/
   	
  })

	var viewFullScreen = document.getElementById("view-fullscreen");
	if (viewFullScreen) {
    viewFullScreen.addEventListener("click", function () {
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

			stage.toggleFullscreen(document.body.viewport);
    });
	}

});

function loadDataFromPlot(prot, x, y, figure) {

	var frame = arrayDistancesFrames.indexOf(y);
	var maxv = Math.max(...arrayDistancesFrames);
	var minv = Math.min(...arrayDistancesFrames);

	drawGraph(prot, figure, 'distances', 'Distance between ', ['Snapshot index', 'Distance (Ångströms)'], 0, (frame + 1), minv, (frame + 1), maxv);

	$("#distance-label").html("Distance: <strong>" + y + " Å</strong>");

	goToFrame(arrayDistancesFrames.indexOf(y));

}

function drawGraph(prot, figure, name, maintit, titles, type, x0, y0, x1, y1) {

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
		title: maintit + figure.rp1 + '-' + figure.rp2 + ' proteins',
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
		
			load3D(prot);

		});

	}else{

		Plotly.newPlot('endToEndPlotDiv', data, layout);

	}

		endToEndPlotDiv.on('plotly_click', function(data){
		loadDataFromPlot(prot, data.points[0].x, data.points[0].y, figure);
		
	});

}

function loadEndToEndPlot(prot, name, maintit, titles) {

	$("#loading-plot").show();

	Plotly.d3.json(baseURL + 'backoutput/flex/' + projectID + '/' + strType+ '/contacts-dist/contacts-dist/' + prot[0] + '-' + prot[1], function(figure) {
	if(!figure.error) {

		arrayDistancesFrames = figure.py;
		arrayDistancesSort = figure.adistsort;
		var frameinit = (arrayDistancesFrames.indexOf(arrayDistancesSort[0]) + 1);

		var maxv = Math.max(...arrayDistancesFrames);
		var minv = Math.min(...arrayDistancesFrames);

		drawGraph(prot, figure, name, maintit, titles, 1, frameinit, minv, frameinit, maxv);
		
	}else{
		$("#loading-plot").html("Error loading plot data. Please, try later.");
	}

	});

}


