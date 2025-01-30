var baseURL = $("#base-url").val();
var projectID = $("#projectID").val();
var strType = $("#strType").val();


function loadScatterPlot(subsection, type, titles, names, maintit, flag) {

	$("#loading-" + subsection).show();

	Plotly.d3.json(baseURL + 'backoutput/flex/' + projectID  + '/' + strType+ '/circular/' + subsection + '/' + type , function(figure) {
	if(!figure.error) {

	var trace1 = {
		x: figure.x,
		y: figure.y,
		name: names[0],
		mode: 'lines+markers',
		marker: {
			color: "#cc6600"
		},
		error_y: {
			type: 'data',
			array: figure.e1,
			visible: true,
			color: "#cc6600"
		},
		type: 'scatter'
	};

	var data = [trace1];

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
		$("#loading-" + subsection).html("Error loading plot data. Please, try later.");
	}

	});

}


window.addEventListener('resize', function(){ 
	circularPlotDiv.layout.width = $("#circularPlotDiv").width();
	Plotly.redraw(circularPlotDiv);
}, true);


$(document).ready(function() {

	$('html,body').animate({ scrollTop: $("#menu-flex").offset().top}, 10);

	if(strType == 'traj') {
	
		loadScatterPlot('circular', 'rg', ['# of snapshot', 'Radius of gyration (in nm)'], ['Radius of gyration'], 'Radius of gyration', 1);
	
	}

});
