var baseURL = $("#base-url").val();
var projectID = $("#projectID").val();
var strType = $("#strType").val();

function loadScatterPlot(subsection, type, titles, names, maintit, flag) {

	$("#loading-" + subsection).show();

	Plotly.d3.json(baseURL + 'backoutput/flex/' + projectID  + '/' + strType+ '/stiffness/' + subsection + '/' + type , function(figure) {
		//console.log(baseURL + 'backoutput/flex/' + projectID  + '/' + strType+ '/stiffness/' + subsection + '/' + type);
	if(!figure.error) {

	var trace1 = {
		x: figure.labels,
		y: figure.v1,
		name: names[0],
		mode: 'lines+markers',
		marker: {
			color: "#cc6600"
		},
		/*error_y: {
			type: 'data',
			array: figure.e1,
			visible: true,
			color: "#f3c200"
		},*/
		type: 'scatter'
	};

	/*var trace2 = {
		x: figure.labels,
		y: figure.v2,
		name: names[1],
		marker: {
			color: "#525e64"
		},
		error_y: {
			type: 'data',
			array: figure.e2,
			visible: true,
			color: "#525e64"
		},
		type: 'scatter'
	};*/

	var data = [trace1/*, trace2*/];

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
	helicalPlotDiv.layout.width = $("#helicalPlotDiv").width();
	Plotly.redraw(helicalPlotDiv);
}, true);


$(document).ready(function() {

	$('html,body').animate({ scrollTop: $("#menu-flex").offset().top}, 10);

	loadScatterPlot('helical', 'shift_avg', ['Sequence Base Pair', 'Shift (Kcal/mol*Ångströms²)'], ['Shift User-MCDNA Stiffness Average'], 'Base Pair Step Helical Parameters Stiffness Constants: Shift', 1);

	//loadStiffness('shift');

});
