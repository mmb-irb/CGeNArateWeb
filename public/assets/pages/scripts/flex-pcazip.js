var baseURL = $("#base-url").val();
var projectID = $("#projectID").val();
var strType = $("#strType").val();

// DATATABLES

var TableDatatablesPCA = function () {

    var initTable = function () {

        var table = $('#tablePCAZip');

        // begin first table
        datatable = table.DataTable({

            // Internationalisation. For more info refer to http://datatables.net/manual/i18n
            "language": {
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                },
                "emptyTable": "No clusters available",
                "info": "Showing _START_ to _END_ of _TOTAL_ records",
                "infoEmpty": "No files",
                "infoFiltered": "(filtered from _MAX_ total records)",
                "lengthMenu": "Show _MENU_",
                "search": "Search:",
                "zeroRecords": "No matching records found",
                "paginate": {
                    "previous":"Prev",
                    "next": "Next",
                    "last": "Last",
                    "first": "First"
                }
            },

						"paging": false,

            //"bStateSave": true, // save datatable state(pagination, sort, etc) in cookie.
						"bSortClasses": false,

            "lengthMenu": [
                [5, 10, 20],
                [5, 10, 20] // change per page values here
            ],
            // set the initial value
            "pageLength": 5,
            "columnDefs": [
                
            ],
            "order": [
                [0, "asc"]
            ], // set first column as a default sort by asc
						"initComplete": function (settings, json) {
							$("#loading-table").hide();
							table.show();				
   					}
        });

        var tableWrapper = jQuery('#ws-table-wrapper');

    }

    return {

        //main function to initiate the module
        init: function () {
            if (!jQuery().dataTable) {
                return;
            }

            initTable();

        }

    };

}();

// NGL VIEWER

function load3D(id) {

	$("#loading-viewport").show();

	$('#viewport').html('');


	$.ajax({
			
		type: "POST",
		url: baseURL + "path/for/" + projectID,
		success: function(response) {

			var obj = JSON.parse(response);

			var path = obj.PCAZipPath + 'NAFlex_pcazipOut.anim' + id + '.pdb'

			stage = new NGL.Stage( "viewport", { backgroundColor:"#c7ced1", tooltip:false } );
			stage.removeAllComponents();
			stage.loadFile(path , { ext: 'pdb', defaultRepresentation: true, asTrajectory: true } ).then( function( o ){

				traj = o.trajList[0].trajectory;
				var player = new NGL.TrajectoryPlayer( traj, {
						step: 1,
						timeout: 150,
						//interpolateStep: 100,
						start: 0,
						end: traj.numframes,
						interpolateType: "linear",
						mode: "loop",
						direction: "bounce"
				} );

				player.play();

				o.removeAllRepresentations();

				if(resolution == 1) {
				
					o.addRepresentation( "licorice", {
						sele: "not(water or ion)", scale: 1, aspectRatio: 1.5
					} );

				} else {

					o.addRepresentation( "ball+stick", {
						sele: "not .P1", color: "element", radius: .5
					} );

					o.addRepresentation( "spacefill", {
						sele: ".P1", scale: 1, radius:1, color: "element"
					} );

				}

				o.autoView();

				$("#loading-viewport").hide();

			} );

		}

	});

	

}

// PLOTS

function loadPCAPlot(plotID, name, maintit, titles) {

	$("#loading-pcazip").show();

	Plotly.d3.json(baseURL + 'backoutput/flex/' + projectID + '/' + strType+ '/pcazip/' + plotID , function(figure) {
	if(!figure.error) {

		// SCATTER PLOT

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
			title: maintit + plotID,
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
		};

		Plotly.newPlot('pcazipPlotDiv', data, layout).then(function(){
			$("#loading-pcazip").hide();
		});

		// HISTOGRAM PLOT
		
		var traceh = {
			//mode: 'lines',
			line: {
				shape: 'spline',
				color: '#cc6600',
				width: 6
			},
			x: figure.hx,
			y: figure.hy,
			orientation: 'h',
		};

		var datah = [traceh];

		var layouth = {
			xaxis: {
				title: 'density',
				showline: true,
				showgrid: false,
				zeroline: false
			},
			yaxis: {
				side: 'right',
				showline: true,
				showgrid: false,
				zeroline: false
			},
			margin: {
				l:0
			}
		};

		Plotly.newPlot('pcaziphPlotDiv', datah, layouth).then(function(){
			$("#loading-pcaziph").hide();
		});

	}else{
		$("#loading-" + type).html("Error loading plot data. Please, try later.");
	}

	});

}

// UTILITIES

window.addEventListener('resize', function(){ 
	pcazipPlotDiv.layout.width = $("#pcazipPlotDiv").width();
	Plotly.redraw(pcazipPlotDiv);
	pcaziphPlotDiv.layout.width = $("#pcaziphPlotDiv").width();
	Plotly.redraw(pcaziphPlotDiv);
}, true);

$(document).ready(function() {

	$('html,body').animate({ scrollTop: $("#menu-flex").offset().top}, 10);
	
	// init table
	TableDatatablesPCA.init();

	// init PDB view
	load3D('1');

	//***********************************
	//
	loadPCAPlot(1, 'projection', 'Trajectory Projection to Vector ', ['Ensemble (snapshots)', 'Displacement (Ångströms)']);
	//
	//***********************************

	// mark first row
	$('#tablePCAZip > tbody  > tr').each(function() {

		if($("td:first", this)[0].innerText.match(/\b1\b/g)) $(this).addClass('active').siblings().removeClass('active');
	
	});

	$('#tablePCAZip').on('click', '.clickable-row', function(event) {

		//console.log($(this).find('td:eq(0)')[0].innerText);

		var animMode = $(this).find('td:eq(0)')[0].innerText;

		load3D(animMode);
		loadPCAPlot(animMode, 'projection', 'Trajectory Projection to Vector ', ['Ensemble (snapshots)', 'Displacement (Ångströms)']);

  	$(datatable.rows().nodes()).removeClass('active');

		$(this).addClass('active')/*.siblings().removeClass('active')*/;

	});

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
