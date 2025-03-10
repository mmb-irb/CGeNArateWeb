var baseURL = $("#base-url").val();
var projectID = $("#projectID").val();
var strType = $("#strType").val();

function loadBarsPlot(subsection, type, title, names, cols) {

	$("#loading-" + subsection).show();

	Plotly.d3.json(baseURL + 'backoutput/flex/' + projectID  + '/' + strType+ '/curves/' + subsection + '/' + type, function(figure) {
	if(!figure.error) {

	var trace1 = {
		x: figure.labels,
		y: figure.x1,
		
		name: names[0],
		type: 'bar'
	};

	var trace2 = {
		x: figure.labels,
		y: figure.x2,
		
		name: names[1],
		type: 'bar'
	};

	if(cols == 4) {

		var trace3 = {
			x: figure.labels,
			y: figure.x3,
			
			name: names[2],
			type: 'bar'
		};

		var trace4 = {
			x: figure.labels,
			y: figure.x4,
			
			name: names[3],
			type: 'bar'
		};

		var data = [trace1, trace2, trace3, trace4];

	}else{

		var data = [trace1, trace2];

	}

	var layout = {
		legend: {"orientation": "h", y:500},
		barmode: 'stack',
		title: 'Nucleotide Parameter: ' + title,
		autosize: true,
		xaxis: {
			ticks: "",
			title: "Nucleotide Sequence"
		},
		yaxis: {
			ticks: "",
			ticksuffix: " ",
			title: title + " (%)"
		}
	};

	Plotly.newPlot(subsection + 'PlotDiv', data, layout).then(function(){
		$("#loading-" + subsection).hide();
	});

	$('.mt-element-overlay .mt-overlay-1 .mt-overlay.' + subsection).css('opacity', '0');
	$('#' + type).css('opacity', '1');
	
	}else{
		$("#loading-" + subsection).html("Error loading plot data. Please, try later.");
	}
	
	});

}

function loadScatterPlot(subsection, type, titles, names, maintit, flag) {

	$("#loading-" + subsection).show();

	Plotly.d3.json(baseURL + 'backoutput/flex/' + projectID  + '/' + strType+ '/curves/' + subsection + '/' + type , function(figure) {
	if(!figure.error) {

	var trace1 = {
		x: figure.labels,
		y: figure.v1,
		name: names[0],
		mode: 'lines+markers',
		
		error_y: {
			type: 'data',
			array: figure.e1,
			visible: true,
			color: "#cc6600"
		},
		type: 'scatter'
	};

	var data = [trace1];

	/*if(figure.v2) {
		var trace2 = {
			x: figure.labels,
			y: figure.v2,
			name: names[1],
			mode: 'lines+markers',
			
			// error_y: {
			// 	type: 'data',
			// 	array: figure.e2,
			// 	visible: true,
			// 	color: "#525e64"
			// },
			type: 'scatter'
		};

		data.push(trace2);
	}

	if(figure.v3) {
		var trace3 = {
			x: figure.labels,
			y: figure.v3,
			name: names[2],
			mode: 'lines+markers',
			
			// error_y: {
			// 	type: 'data',
			// 	array: figure.e2,
			// 	visible: true,
			// 	color: "#525e64"
			// },
			type: 'scatter'
		};

		data.push(trace3);
	}*/

	var layout = {
		legend: {"orientation": "h", y:500},
		title: maintit,
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
		showlegend: true
	};

	Plotly.newPlot(subsection + 'PlotDiv', data, layout).then(function(){
		$("#loading-" + subsection).hide();
	});

	$('.mt-element-overlay .mt-overlay-1 .mt-overlay.' + subsection).css('opacity', '0');
	$('#' + type).css('opacity', '1');
	
	}else{
		$("#loading-" + type).html("Error loading plot data. Please, try later.");
	}

	});

}


window.addEventListener('resize', function(){ 
	/*backbone_torsionsPlotDiv.layout.width = $("#backbone_torsionsPlotDiv").width();
	Plotly.redraw(backbone_torsionsPlotDiv);*/
	axis_bpPlotDiv.layout.width = $("#axis_bpPlotDiv").width();
	Plotly.redraw(axis_bpPlotDiv);
	/*helical_bpPlotDiv.layout.width = $("#helical_bpPlotDiv").width();
	Plotly.redraw(helical_bpPlotDiv);*/
	helical_bpstepPlotDiv.layout.width = $("#helical_bpstepPlotDiv").width();
	Plotly.redraw(helical_bpstepPlotDiv);
	groovesPlotDiv.layout.width = $("#groovesPlotDiv").width();
	Plotly.redraw(groovesPlotDiv);
}, true);


$(document).ready(function() {

	$('html,body').animate({ scrollTop: $("#menu-flex").offset().top}, 10);

	//if(strType == 'eq') loadBarsPlot('backbone_torsions', 'BI_population', 'BI/BII Population', ['BI', 'BII'], 2);
	loadScatterPlot('axis_bp', 'inclin_avg', ['Sequence Base Pair', 'Inclination (degrees)'], ['Inclination User-CGeNArate Average', 'Inclination ABC Average'], 'Base Pair Helical Parameters: Inclination', 1);
	//if(strType == 'eq') loadScatterPlot('helical_bp', 'shear_avg', ['Sequence Base Pair', 'Shear (Ångströms)'], ['Shear User-CGeNArate Average', 'Shear ABC Average'], 'Base Pair Helical Parameters: Shear', 1);
	loadScatterPlot('helical_bpstep', 'rise_avg', ['Sequence Base Pair Step', 'Rise (Ångströms)'], ['Rise User-CGeNArate Average', 'Rise ABC Average', 'Rise X-Ray Average'], 'Base Pair Step Helical Parameters: Rise', 1);
	loadScatterPlot('grooves', 'majd_avg', ['Sequence Base Pair', 'Major Groove Depth (Ångströms)'], ['Major Groove User-CGeNArate Average'], 'Base Pair Helical Parameter: Major Groove', 1);

	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
  	var target = $(e.target).attr("href").substr(1) // activated tab
		switch(target) {
			case 'axis_bp':
				loadScatterPlot('axis_bp', 'inclin_avg', ['Sequence Base Pair', 'Inclination (degrees)'], ['Inclination User-CGeNArate Average', 'Inclination ABC Average'], 'Base Pair Helical Parameters: Inclination', 1);
			break;
			case 'helical_bp':
				loadScatterPlot('helical_bp', 'shear_avg', ['Sequence Base Pair', 'Shear (Ångströms)'], ['Shear User-CGeNArate Average', 'Shear ABC Average'], 'Base Pair Helical Parameters: Shear', 1);
			break;
			case 'helical_bpstep':
				loadScatterPlot('helical_bpstep', 'rise_avg', ['Sequence Base Pair Step', 'Rise (Ångströms)'], ['Rise User-CGeNArate Average', 'Rise ABC Average', 'Rise X-Ray Average'], 'Base Pair Step Helical Parameters: Rise', 1);
			break;
			case 'grooves':
				loadScatterPlot('grooves', 'majd_avg', ['Sequence Base Pair', 'Major Groove Depth (Ångströms)'], ['Major Groove User-CGeNArate Average'], 'Base Pair Helical Parameter: Major Groove', 1);
			break;
		}
	});

});
