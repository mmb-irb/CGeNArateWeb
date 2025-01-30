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
		$("#heatmap").show();
		axisLabels = ['Sequence', 'Sequence'];
		loadHeatMap('mean', 'Distances (Ångströms)', axisLabels, 'NUC-NUC');
		$("#heatmap-type").html("Nucleotide - Nucleotide");
	} else if(typeOfContact == 'PROT-NUC') {
		$("#heatmap").hide(); 
		$("#3d-view").show();
		$('#submit-prot-nuc').prop('disabled', true);
		$('input[type=radio][name=proteinIDradio]').prop('checked', false);
		$("#heatmap-type").html("Protein - Nucleotide");
	} else if(typeOfContact == 'PROT-PROT') {
		$("#heatmap").hide(); 
		$("#3d-view").show();
		$('#submit-prot-prot').prop('disabled', true);
		$('input[type=checkbox][name=proteinIDcheckbox]').prop('checked', false);
		$("#heatmap-type").html("Protein - Protein");
	}

	if((typeOfContact == 'PROT-NUC') || (typeOfContact == 'PROT-PROT')) {

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
								sele: "not(water or ion or :A or :B)", scale: 1.5, aspectRatio: 1.5, color: "sstruc"
							} );
							o.addRepresentation( "licorice", {
								sele: ":A or :B", scale: 1.5, aspectRatio: 1.5
							} );

						}else{

							o.addRepresentation( "cartoon", {
								sele: "not(water or ion or :A or :B)", scale: 1.5, aspectRatio: 1.5, color: "sstruc"
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

});


