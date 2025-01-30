var baseURL = $("#base-url").val();
var projectID = $("#projectID").val();
var strType = $("#strType").val();
var tool = $("#tool").val();

function loadScatterPlot(subsection, type, titles, names, maintit, shape) {

	$("#loading-" + type).show();

	Plotly.d3.json(baseURL + 'backoutput/flex/' + projectID  + '/' + strType+ '/energy/' + subsection + '/' + type , function(figure) {

	if(!figure.error) {

		var trace1 = {
			//x: figure.x1,
			y: figure.y2,
			name: names[1],
			mode: 'lines',
			line: {shape: shape},
			marker: {
				color: "#cc6600"
			}
		};

		var data = [trace1];

		/*if(type != "elen") {
			var trace2 = {
				//x: figure.x1,
				y: figure.y1,
				name: names[0],
				mode: 'lines',
				line: {shape: shape},
				marker: {
					color: "#525e64"
				}
			};

			data.push(trace2);

		}*/

		//var data = [trace1, trace2];

		var layout = {
			//legend: {"orientation": "h", y:500},
			title: maintit,
			autosize: true,
			xaxis: {
				title: titles[0],
				showline: true,
				showgrid: false,
				zeroline: false,
				range:[1, figure.y1.length]
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

		/*$('.mt-element-overlay .mt-overlay-1 .mt-overlay.' + subsection).css('opacity', '0');
		$('#' + type).css('opacity', '1');*/
	
	}else{
		$("#loading-" + type).html("Error loading plot data. Please, try later.");
	}

	});

}

function loadScatterProteinsPlot(subsection, type, titles, maintit, shape) {

	$("#loading-" + type).show();

	Plotly.d3.json(baseURL + 'backoutput/flex/' + projectID  + '/' + strType+ '/energy/' + subsection + '/' + type , function(figure) {

	if(!figure.error) {

		var xax = [];
		var yax = [];
		var prt = [];

		$.each(figure.xaxis, function (variable, content) {
			xax.push(content);
		});

		$.each(figure.yaxis, function (variable, content) {
			yax.push(content);
		});

		$.each(figure.proteins, function (variable, content) {
			prt.push(content);
		});

		var traces = [];
		var maxlen = 0;
		$.each(xax, function(k, v) {

			if(v.length > maxlen) maxlen = v.length;

			traces.push({
				x: v,
				y: yax[k],
				name: prt[k],
				mode: 'lines',
				line: {shape: shape},
			});

		});

		var data = traces;

		var layout = {
			title: maintit,
			autosize: true,
			xaxis: {
				title: titles[0],
				showline: true,
				showgrid: false,
				zeroline: false,
				range:[1, maxlen] 
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

	}else{
		$("#loading-" + type).html("Error loading plot data. Please, try later.");
	}

	});

}

window.addEventListener('resize', function(){
	if($("#elenPlotDiv").length) {
		elenPlotDiv.layout.width = $("#elenPlotDiv").width();
		Plotly.redraw(elenPlotDiv);
	}
	if($("#unboundPlotDiv").length) {
		unboundPlotDiv.layout.width = $("#unboundPlotDiv").width();
		Plotly.redraw(unboundPlotDiv);
	}
	if($("#dnaprotPlotDiv").length) {
		dnaprotPlotDiv.layout.width = $("#dnaprotPlotDiv").width();
		Plotly.redraw(dnaprotPlotDiv);
	}
}, true);

$(document).ready(function() {

	$('html,body').animate({ scrollTop: $("#menu-flex").offset().top}, 10);

	if($("#elenPlotDiv").length) {
		loadScatterPlot('energy', 'elen', 
				['Index of snapshot', 'Energy (kcal/mol)'], 
				["Elastic energy", "Elastic energy per bp"], 
				'Elastic energy of DNA along trajectory',"spline");
	}

	if(tool == '3') {
	
		if($("#unboundPlotDiv").length) {
			loadScatterPlot('energy', 'unbound', 
				['Index of snapshot', 'Energy (kcal/mol)'], 
				["Elastic energy", "Elastic energy per bp"], 
				'Elastic Energy of DNA not bound to a protein along trajectory',"spline");
		}

		if($("#dnaprotPlotDiv").length) {
			loadScatterProteinsPlot('energy', 'dnaprot', 
				['bp', 'Energy (kcal/mol)'], 
				'Elastic Energy of Protein-Bound DNA',"scatter");
		}

	}

});
