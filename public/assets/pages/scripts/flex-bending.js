var baseURL = $("#base-url").val();
var projectID = $("#projectID").val();
var strType = $("#strType").val();


function loadScatterPlot(subsection, type, titles, names, maintit, shape, numPlots) {

	$("#loading-" + type).show();

	Plotly.d3.json(baseURL + 'backoutput/flex/' + projectID  + '/' + strType+ '/bending/' + subsection + '/' + type , function(figure) {
	if(!figure.error) {

	var trace1 = {
		x: figure.x1,
		y: figure.y1,
		name: names[0],
		mode: 'lines',
		line: {shape: shape},
		
	};

	var data = [trace1];

	if(numPlots > 1) {

		var trace2 = {
			x: figure.x2,
			y: figure.y2,
			name: names[1],
			mode: 'lines',
			line: {shape: shape},
			
		};

		var data = [trace1, trace2];

	}

	if(numPlots > 2) {

		var trace3 = {
			x: figure.x3,
			y: figure.y3,
			name: names[2],
			mode: 'lines',
			line: {shape: shape},
			
		};

		var data = [trace1, trace2, trace3];

	}

	if(numPlots > 3) {

		var trace4 = {
			x: figure.x4,
			y: figure.y4,
			name: names[3],
			mode: 'lines',
			line: {shape: shape},
			
		};

		var data = [trace1, trace2, trace3, trace4];

	}

	if(numPlots > 4) {

		var trace5 = {
			x: figure.x5,
			y: figure.y5,
			name: names[4],
			mode: 'lines',
			line: {shape: shape},
			
		};

		var data = [trace1, trace2, trace3, trace4, trace5];

	}

	if(numPlots > 5) {

		var trace6 = {
			x: figure.x6,
			y: figure.y6,
			name: names[5],
			mode: 'lines',
			line: {shape: shape},
			
		};

		var data = [trace1, trace2, trace3, trace4, trace5, trace6];

	}

	if(numPlots > 6) {

		var trace7 = {
			x: figure.x7,
			y: figure.y7,
			name: names[6],
			mode: 'lines',
			line: {shape: shape},
			
		};

		var data = [trace1, trace2, trace3, trace4, trace5, trace6, trace7];

	}

	if(numPlots > 7) {

		var trace8 = {
			x: figure.x8,
			y: figure.y8,
			name: names[7],
			mode: 'lines',
			line: {shape: shape},
			
		};

		var data = [trace1, trace2, trace3, trace4, trace5, trace6, trace7, trace8];

	}

	if(numPlots > 8) {

		var trace9 = {
			x: figure.x9,
			y: figure.y9,
			name: names[8],
			mode: 'lines',
			line: {shape: shape},
			
		};

		var data = [trace1, trace2, trace3, trace4, trace5, trace6, trace7, trace8, trace9];

	}


	var layout = {
		//legend: {"orientation": "h", y:500},
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

	Plotly.newPlot(type + 'PlotDiv', data, layout).then(function(){
		$("#loading-" + type).hide();
	});

	$('.mt-element-overlay .mt-overlay-1 .mt-overlay.' + subsection).css('opacity', '0');
	$('#' + type).css('opacity', '1');
	
	}else{
		$("#loading-" + type).html("Error loading plot data. Please, try later.");
	}

	});

}

function loadScatterPlot2Axis(subsection, type, titles, names, maintit, shape, numPlots) {

	$("#loading-" + type).show();

	Plotly.d3.json(baseURL + 'backoutput/flex/' + projectID  + '/' + strType+ '/bending/' + subsection + '/' + type , function(figure) {
	if(!figure.error) {

	var trace1 = {
		x: figure.x1,
		y: figure.y1,
		name: names[0],
		mode: 'lines',
		line: {shape: shape},
		
	};

	var data = [trace1];

	if(numPlots > 1) {

		var trace2 = {
			x: figure.x2,
			y: figure.y2,
			name: names[1],
			mode: 'lines',
			line: {shape: shape},
			
		};

		var data = [trace1, trace2];

	}

	if(numPlots > 2) {

		var trace3 = {
			x: figure.x3,
			y: figure.y3,
			yaxis: 'y2',
			name: names[2],
			mode: 'lines',
			line: {shape: shape},
			
		};

		var data = [trace1, trace2, trace3];

	}

	if(numPlots > 3) {

		var trace4 = {
			x: figure.x4,
			y: figure.y4,
			yaxis: 'y2',
			name: names[3],
			mode: 'lines',
			line: {shape: shape},
			
		};

		var data = [trace1, trace3, trace2, trace4];

	}

	if(numPlots > 4) {

		var trace5 = {
			x: figure.x5,
			y: figure.y5,
			yaxis: 'y2',
			name: names[4],
			mode: 'lines',
			line: {shape: shape},
			
		};

		var data = [trace1, trace2, trace3, trace4, trace5];

	}

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
		yaxis2: {
			title: titles[2],
			overlaying: 'y',
			side: 'right',
			showline: true,
			showgrid: false,
			zeroline: false
		},
		showlegend: true
	};

	Plotly.newPlot(type + 'PlotDiv', data, layout).then(function(){
		$("#loading-" + type).hide();
	});

	$('.mt-element-overlay .mt-overlay-1 .mt-overlay.' + subsection).css('opacity', '0');
	$('#' + type).css('opacity', '1');
	
	}else{
		$("#loading-" + type).html("Error loading plot data. Please, try later.");
	}

	});

}

window.addEventListener('resize', function(){ 
	distributionPlotDiv.layout.width = $("#distributionPlotDiv").width();
	Plotly.redraw(distributionPlotDiv);
	individualPlotDiv.layout.width = $("#individualPlotDiv").width();
	Plotly.redraw(individualPlotDiv);
	if($("#fiberPlotDiv").length) {
		fiberPlotDiv.layout.width = $("#fiberPlotDiv").width();
		Plotly.redraw(fiberPlotDiv);
	}
	if($("#fiberalongPlotDiv").length) {
		fiberalongPlotDiv.layout.width = $("#fiberalongPlotDiv").width();
		Plotly.redraw(fiberalongPlotDiv);
	}
}, true);


$(document).ready(function() {

	$('html,body').animate({ scrollTop: $("#menu-flex").offset().top}, 10);

	if(strType == 'traj') {
	
		loadScatterPlot('bending', 'distribution', 
				['Bending (in deg)', 'Density'], 
				["5 bp total", "10 bp total", "5 bp -xz", "10 bp -xz", "5 bp -yz", "10 bp -yz", "xz (whole fiber)", "yz (whole fiber)", "total (whole fiber)"], 
				'Bending distribution of 5/10 bp segments and the whole fiber',"spline", 9);

		loadScatterPlot('bending', 'individual', 
				['# bp', 'Bending (in deg)'], 
				["5 bp -xz", "5 bp -yz", "10 bp -xz", "10 bp -yz"], 
				'Bending of 5/10 bp pieces in xz/yz-direction along sequence',"linear", 4);

		loadScatterPlot('bending', 'fiber', 
				['Contribution (in %)', 'Density'], 
				["xz-bending", "yz-bending"], 
				'Distribution of contributions of xz- and yz-bending of total bending of whole fiber ensemble',"spline", 2);

		loadScatterPlot2Axis('bending', 'fiberalong', 
				['number of snapshot', 'Contribution (in %)', 'Bending (in deg)'], 
				["xz-bending (%)", "yz-bending (%)", "xz-bending (deg)", "yz-bending (deg)", "total-bending (deg)"], 
				'Bending of whole fiber along trajectory',"linear", 5);
	
	} else {

		loadScatterPlot('bending', 'distribution', 
				['Bending (in deg)', 'Density'], 
				["5 bp total", "10 bp total", "5 bp -xz", "10 bp -xz", "5 bp -yz", "10 bp -yz"], 
				'Bending distribution of 5/10 bp segments',"spline", 6);

		loadScatterPlot('bending', 'individual', 
				['# bp', 'Bending (in deg)'], 
				["5 bp -xz", "5 bp -yz", "10 bp -xz", "10 bp -yz"], 
				'Bending of 5/10 bp segments in xz/yz-direction along sequence',"linear", 4);

	}

});
