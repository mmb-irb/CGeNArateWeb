var baseURL = $('#base-url').val();
var sampleType = $('#sample-type').val();
var validate_input_text;


var FormValidation = function () {

    $('.operations').change(function() {

			var id = $(this).attr('id').substring(10,11);

			var array_selecteds = $(this).val();

			if (array_selecteds !== null) {

				if(array_selecteds.indexOf("createTrajectory") != -1) {
					$('#fg_numStruct' + id).show();
					$('.numStruct' + id).prop('disabled', false);
				} else {
					$('#fg_numStruct' + id).hide();
					$('.numStruct' + id).prop('disabled', true);
				}
				
			}else{
				
				$('#fg_numStruct' + id).hide();
				$('.numStruct' + id).prop('disabled', true);

			}

		});

    $.validator.addMethod("regx", function(value, element, regexpr) { 
		if(!value) return true;
	    return regexpr.test(value);
	});

		// Form with file
    var handleValidationForm2 = function() {
				
			function compareByPosition(a,b) {
				if (a.position < b.position)
					return -1;
				if (a.position > b.position)
					return 1;
				return 0;
			}

			var form = $('#form_input_text');
			//var error = $('.alert-form-error1', form);
			
			$('#seqtxt').maxlength({
					warningClass: "label label-warning",
					limitReachedClass: "label label-danger",
					alwaysShow: true
			});

			validate_input_text = form.validate({
					errorElement: 'span', //default input error message container
					errorClass: 'help-block', // default input error message class
					focusInvalid: false, // do not focus the last invalid input
					ignore: [],
					messages: {
						seqtxt: {
							regx: "Invalid DNA sequence or length is less than 15 characters.",
								required: "The sequence text is required.",
						},
						email: {
								//required: "Email is required.",
								email: "Email format invalid."
						},
						tool: {
								required: "Please select the tool you want to execute.",
						},
						resolution: {
								required: "Please select the resolution.",
						},
						nuclpos: {
								regx: "You must use the next format: '10 15 23 53'.",
								required: "The nucleosomes positions are required.",
						},
						deltaLN: {
								required: "Please select the Delta linking number.",
						},
						iterStruct: {
								digits: "Please enter an integer number.",
						},
						/*type: {
								required: "Please select the type you want to execute.",
						},*/

					},
					rules: {
							seqtxt: {
								regx: /^[CAGTcagt]{15,}$/,
									required: true,
							},
							email: {
									email: true,
									//required: true
							},
							numStruct: {
								required:true, 
								range: [1, 500]
							},
							iterStruct: {
								required:true,
								digits: true								 
							},
							tool: {
								required: true,
							},
							resolution: {
								required: true,
							},
							nuclpos: {
								regx: /^\d+(?:\s\d+){0,}$/,
								required: true,
							},
							deltaLN: {
								required: true,
							},
						/*	type: {
								required: true,
						},*/

					},

					invalidHandler: function (event, validator) { //display error alert on form submit
							//error.show();
							//App.scrollTo(error, -200);
					},

					errorPlacement: function (error, element) { // render error placement for each input type
						
						if (element.closest('.input-icon').size() === 1) {
									error.insertAfter(element.closest('.input-icon'));
							} else {
									if($(element).parent().hasClass('btn-file')) {
										error.insertAfter($(element).parent().parent().parent());
									}else{
										error.insertAfter(element);
									}
							}
					},

					highlight: function (element) { // hightlight error inputs

							if($(element).hasClass('protein-id') || $(element).hasClass('protein-position')) {
								$(element).parent().parent().addClass('has-error');							
							}

							$(element)
									.closest('.form-group').addClass('has-error'); // set error class to the control group
					},

					unhighlight: function (element) { // revert the change done by hightlight
							$(element)
									.closest('.form-group').removeClass('has-error'); // set error class to the control group
					},

					success: function (label, e) {
						$(e).parent().removeClass('has-error');
						$(e).parent().parent().removeClass('has-error');
						$(e).parent().parent().parent().removeClass('has-error');

							/*label
									.closest('.form-group').removeClass('has-error'); // set success class to the control group*/
					},

					submitHandler: function (form) {
						//error.hide();
				
						/*var inputFile = document.getElementById("pdbfile");
						var pdbFile = inputFile.files[0];	*/
						var formData = new FormData(document.getElementById('form_input_text'));  

						//for (var [key, value] of formData.entries()) console.log(key + ": " + value);
						
						var protValues = [];

						for (var [key, value] of formData.entries()) {
							if(key.match(/protein\[[0-9]\]\[position\]/g)) {
								protValues.push({code:'', position:0, length:0});
							}
						}

						for (var [key, value] of formData.entries()) {
							if(key.match(/protein\[[0-9]\]\[code\]/g)) {
								var index = parseInt(key.substring(key.indexOf("[") + 1, key.indexOf("]")));
								protValues[index].code = value;
							}
							if(key.match(/protein\[[0-9]\]\[position\]/g)) {
								var index = parseInt(key.substring(key.indexOf("[") + 1, key.indexOf("]")));
								protValues[index].position = parseInt(value);
							}
							if(key.match(/protein\[[0-9]\]\[length\]/g)) {
								var index = parseInt(key.substring(key.indexOf("[") + 1, key.indexOf("]")));
								protValues[index].length = parseInt(value); 
							}
						}

						var firstFreePos = 3;
						var submitErr = false;
						var seqLength = $('#seqtxt').val().length;

						protValues.sort(compareByPosition);

						//console.log(protValues);

						$.each(protValues, function(i, v) {
							// check if position is correct
							if(firstFreePos > v.position) {
								submitErr = true;
								//console.log("Error, position of protein not correct", v.code);
								//$('#modalErrProts .modal-body').html('The position of the protein ' + v.code.toUpperCase() + ' is not correct. Remember that the proteins must be located sequentially, the second one must be after the first one and successively.');
								$('#modalErrProts .modal-body #error-description').html('There\'s an overlapping between two or more proteins. Please take care of the <strong>sequence length</strong> and the <strong>proteins lengths</strong> and <strong>positions</strong>. Remember that, <strong>between proteins</strong>, there must be <strong>at least two nucleotides</strong>.');
								getProteinsPositions();
								$('#loading-block').show();
								$('#blockPlotDiv').show();
						 		$('#modalErrProts').modal({ show: 'true' });
								return false;
							}
							// check if lenght is correct
							console.log(v.position, v.length, seqLength);
							if((v.position + v.length + 2) > seqLength) {
								submitErr = true;
								//console.log("Error, protein is too long", v.code);
								$('#modalErrProts .modal-body #error-description').html('The protein <strong>' + v.code.toUpperCase() + ' is too long</strong>. Please choose a shorter one or <strong>add nucleotides</strong> to the sequence. Remember that <strong>the end of the protein</strong> must be <strong>at least two nucleotides</strong> before the end of the sequence.');
								getProteinsPositions();
								$('#loading-block').show();
								$('#blockPlotDiv').show();
						 		$('#modalErrProts').modal({ show: 'true' });
								return false;
							}
							// update firstFreePos
							firstFreePos = v.position + v.length + 2;
						});

						if(!submitErr) { 

							//console.log("??");

							// init uploading
							App.blockUI({
								boxed: true,
								message: 'Uploading data. Please don\'t close the window.'
							});

							$.ajax({
								type: "POST",
								url: baseURL + "upload",
								data: formData,
								contentType: false,
								processData: false,
								success: function (output) {

									//console.log(output);

									out = $.parseJSON(output);

									if(out.status == 1) {

										App.blockUI({boxed: true, message: 'Running Job. Please don\'t close the window.'});
										location.href = baseURL + "output/summary/" + out.uid;

									}else{
									
										setTimeout(function(){ 
											App.unblockUI();
											$('.alert-form-error2').show();
											$('.alert-form-error2 span').text(out.msg);
										}, 500);

									}

								},
								error: function (msg) {
									console.log("Error", msg);
								}
							});

						}

						//App.unblockUI();
				}

			});

			/*$("#operations2").rules("add", {
				required:true,
				messages: {
					required: "Please select at least one operation to perform.",
				}
			});*/

			$('.protein-id').each(function () {
        $(this).rules("add", {
            required:true,
        });
    	});

			$(".protein-position").each(function () {
				$(this).rules("add", {
            required:true,
        });
			});
			
    }

    return {
			//main function to initiate the module
			init: function () {

					handleValidationForm2();

			}

    };

}();

// detectar a l'obrir la tab i cridar el segon select2 llavors perquè no es talli
var Select2Init = function() {

	var handleSelect2 = function() {

		$(".operations").select2({
			placeholder: "Select operations",
			width: '100%',
			minimumResultsForSearch: 1
		});


		$('.operations').on('change', function() {
			if($(this).find('option:selected').length > 0) {
				$(this).parent().parent().removeClass('has-error');
				$(this).parent().parent().find('.help-block').hide();
			}
		});

	}

	return {
		//main function to initiate the module
		init: function() {
				handleSelect2();
		}

  };

}();

var countProteins = 1;

var FormRepeater = function () {

    return {
        //main function to initiate the module
        init: function () {
        	$('.mt-repeater').each(function(){
          	$(this).repeater({
        			show: function () {

        				if(countProteins < 10) {
        				
									$(this).slideDown();
									$('.protein-id', this).prop('disabled', false);

									loadProteinFields();

									countProteins ++;

								}
		          },

		          hide: function (deleteElement) {
		          	//if(confirm('Are you sure you want to delete this element?')) {
		            	$(this).slideUp(deleteElement);
		            //}
		          },

							/*defaultValues: {
                'text-input': 'foo'
            	},*/

							isFirstItemUndeletable: true

        		});
        	});
        }

    };

}();

/*String.prototype.highLightChars = function(init, end, length, id, color) {  
	return this.substring(0, (init - 1)) + 
		'<span style="background-color:' + color + '" class="tooltips" data-container="body" data-html="true" data-placement="top" data-original-title="' + id + '">' + this.substring((init - 1), (end - 1)) + 
		'</span>' + this.substring((end - 1), length);
}

function changeProtPos(obj) {

	var name = obj.attr("name");
	var index = parseInt(name.substring(name.indexOf("[") + 1, name.indexOf("]")));	
	var code = $('input[name="protein[' + index + '][code]"]').val();
	var length = parseInt($('input[name="protein[' + index + '][length]"]').val());
	var position = parseInt(obj.val());

	var sequence = $("#seqprots").text();
	
	var higlightedText = sequence.highLightChars(position, (position + length), sequence.length, code, "#f3c200");
	$("#seqprots").html(higlightedText);
	$(".tooltips").tooltip();

	//console.log(obj.val(), index, code, length, position);

}*/

function getProteinsPositions() {

	proteinsPositions = [];

	if($('.protein-position').length) var n = '';
	else if($('.protein-position-sample').length) var n = '-sample';

	$('.protein-position' + n).each(function() {

		var pos = parseInt($(this).val()) - 1;
		var len = parseInt($(this).parent().parent().find('.col-md-2').find('.protein-length' + n).val());
		var code = $(this).parent().parent().find('.col-md-2').find('.protein-code' + n).val();

		var prot = {
			pos1: pos,
			pos2: (pos + len),
			id: code
		}; 

		proteinsPositions.push(prot);

	});

	//console.log(proteinsPositions);

}

var affinityParams = [];
var posSelected = 1;
var protIndex = 0;
var proteinsPositions = [];
var randomColors = ['#0a08da', '#aa0b39', '#4ccd13', '#807270', '#f1d303', '#10b6d0', '#aa0b39', '#04a280', '#5dd556', '#8b2ec2'];

function loadProteinFields() {

	$('.view-affinity').each(function() {

		$(this).on('click', function() {

			getProteinsPositions();

			// checking if sequence empty
			if($("#seqtxt").val() != "") {

				// checking if sequence is CAGT and longer or equal than 15
				if($("#seqtxt").val().toUpperCase().match(/^[CAGTcagt]{15,}$/g)) {

					var nam = $(this).attr("name");
					var index = parseInt(nam.substring(nam.indexOf("[") + 1, nam.indexOf("]")));
					var seq = $("#seqtxt").val().toUpperCase();
					var prot = $("input[name='protein[" + index + "][code]']").val();
					var len = parseInt($("input[name='protein[" + index + "][length]']").val());
					var pos = parseInt($("input[name='protein[" + index + "][position]']").val());
					if (pos == '') pos = 3;

					// checking if protein is not outside the sequence
					if(seq.length > (pos + len)) {

						$("#loading-affinity").show();
						$("#affinityPlotDiv").html('');

						posSelected = pos;
						protIndex = index;

						affinityParams = [];
						affinityParams.push(seq, prot, pos);

						$('#modalAffinity .modal-title').html('Affinity for protein ' + prot);
						$('#modalAffinity').modal({ show: 'true' });

					} else {

						$('#modalErrProts .modal-body #error-description').html('You are trying to insert a protein longer than the sequence.');
						$('#loading-block').show();
						$('#blockPlotDiv').show();
						$('#modalErrProts').modal({ show: 'true' });	

					}

				} else  {

					$('#modalErrProts .modal-body #error-description').html('Sequence format not correct or too short (minimum lenght: 15 characters).');
					$('#loading-block').hide();
					$('#blockPlotDiv').hide();
					$('#modalErrProts').modal({ show: 'true' });

				}

			} else {

				$('#modalErrProts .modal-body #error-description').html('You should fill the DNA sequence before calculating the protein affinity.');
				$('#loading-block').hide();
				$('#blockPlotDiv').hide();
				$('#modalErrProts').modal({ show: 'true' });

			}

		});
	});

	$('.protein-id').each(function() {
			
		$(this).on('change', function() {

			if($(this).val() != "") {

				var v = $(this).val().split(" ");
	
				$(this).parent().parent().find('.col-md-2').find('.protein-code').val(v[0]);
				$(this).parent().parent().find('.col-md-2').find('.protein-length').val(v[1]);
				$(this).parent().parent().find('.col-md-2').find('.protein-position').val(3);

				$(this).parent().parent().find('.col-md-2').find('.view-pdb').attr("href", "javascript:previewNGL('" + v[0]  + "');");

				$(this).parent().parent().find('.col-md-2').show();
				$(this).parent().parent().find('.col-md-1').show();

				$(this).parent().parent().find('.col-md-2').find('.protein-code').prop('disabled', false);
				$(this).parent().parent().find('.col-md-2').find('.protein-length').prop('disabled', false);
				$(this).parent().parent().find('.col-md-2').find('.protein-position').prop('disabled', false);

				$.validator.addClassRules("protein-id", { required: true, }); 
				$.validator.addClassRules("protein-position", { required: true, });

				$(".tooltips").tooltip();

			}else{

				$(this).parent().parent().find('.col-md-2').hide();

				$(this).parent().parent().find('.col-md-2').find('.protein-code').prop('disabled', true);
				$(this).parent().parent().find('.col-md-2').find('.protein-length').prop('disabled', true);
				$(this).parent().parent().find('.col-md-2').find('.protein-position').prop('disabled', true);

			}

		});

	});

}

function disableProts() {

	$('.protein-id').each(function() { 
		$(this).prop('disabled', true);
	});
	$('.protein-code').each(function() { 
		$(this).prop('disabled', true);
	});
	$('.protein-length').each(function() { 
		$(this).prop('disabled', true);
	});
	$('.protein-position').each(function() { 
		$(this).prop('disabled', true);
	});

}

function enableFakeProts() {

	$('.protein-id-sample').each(function() { 
		$(this).prop('disabled', false);
	});
	$('.protein-code-sample').each(function() { 
		$(this).prop('disabled', false);
	});
	$('.protein-length-sample').each(function() { 
		$(this).prop('disabled', false);
	});
	$('.protein-position-sample').each(function() { 
		$(this).prop('disabled', false);
	});

}

function disableFakeProts() {

	$('.protein-id-sample').each(function() { 
		$(this).prop('disabled', true);
	});
	$('.protein-code-sample').each(function() { 
		$(this).prop('disabled', true);
	});
	$('.protein-length-sample').each(function() { 
		$(this).prop('disabled', true);
	});
	$('.protein-position-sample').each(function() { 
		$(this).prop('disabled', true);
	});

}

function drawPlot(figure) {

	var min = Math.min(...figure.py);
	var max = Math.max(...figure.py);
	var len = figure.py.length;

	var arrSeq = $("#seqtxt").val().split("");

	var labelsx = [];
	var c = 0;
	$.each(figure.px, function(k, v) {

		labelsx.push(arrSeq[c].toUpperCase() + '-' + v);
		c++;

	});

	//console.log(labelsx);

	var trace = {
		x: labelsx,
		y: figure.py,
		name: "Affinity",
		mode: 'lines+markers',
		//line: {shape: "lines+markers"},
		marker: {
			color: "#f3c200"
		},
		type:"scatter"
	};
	
	var data = [trace];

	// shapes + annotations
	var lineMinVal = {
				xref: 'paper',
				yref: 'y',
				type: 'line',
				y0: min,
				x0: 0,
				y1: min,
				x1: 1,
				line: {
					color: '#4f79bc',
					width: 2,
				}
			};

	var lineCurPos = {
				xref: 'x',
				yref: 'paper',
				type: 'line',
				y0: 0,
				x0: posSelected - 1,
				y1: 1,
				x1: posSelected - 1,
				line: {
					color: '#cc6600',
					width: 3,
					dash: 'dash'
				}
			};

	var arrShapes = [lineMinVal, lineCurPos];
	var arrAnnotations = [];

	$.each(proteinsPositions, function (k, item) {

		var block = {
				type: 'rect',
				xref: 'x',
				yref: 'paper',
				x0: item.pos1,
				y0: 0,
				x1: item.pos2,
				y1: 1,
				fillcolor: randomColors[k],
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
			font: { color:randomColors[k] },
			arrowcolor: randomColors[k],
      arrowhead: 2,
      ax: 0,
      ay: -30
    };

		arrAnnotations.push(annotation);

	});

	var layout = {
		autosize: true,
		title: 'Click on the position where you want to insert the protein',
		xaxis: {
			title: "Number of base pair",
			showline: true,
			showgrid: false,
			zeroline: false,
		},
		yaxis: {
			title: "Energy (kcal/mol)",
			showline: true,
			showgrid: false,
			zeroline: false
		},
		showlegend: false,
		shapes: arrShapes,
		annotations: arrAnnotations
	};

	Plotly.newPlot('affinityPlotDiv', data, layout).then(function(){
		$("#loading-affinity").hide();
		$("#shape-legend").show();
	});

	affinityPlotDiv.on('plotly_click', function(data){
		//loadDataFromPlot(data.points[0].x, data.points[0].y, figure);
		//console.log(protIndex);

		if($("#sample-type").val() == "") {
			$("input[name='protein[" + protIndex + "][position]']").val(data.points[0].x.split('-')[1]);
		}

		$('#modalAffinity').modal('toggle');

	});

}

function drawPlotProteins() {

	var arrSeq = $("#seqtxt").val().split("");

	var labelsx = [];
	$.each(arrSeq, function(k, v) {

		labelsx.push(v.toUpperCase() + '-' + (k + 1));

	});

	var trace = {
		x: labelsx,
		//y: figure.py,
		name: "Affinity",
		mode: 'lines+markers',
		opacity:0,
		marker: {
			color: "#f3c200",
		},
		type:"scatter",
		hoverinfo:"x"
	};
	
	var data = [trace];

	// shapes + annotations
	
		var lineCurPos = {
			xref: 'x',
			yref: 'paper',
			type: 'line',
			y0: 0,
			x0: labelsx.length - 1,
			y1: 1,
			x1: labelsx.length - 1,
			line: {
				color: '#cc0000',
				width: 3,
				dash: 'dash'
			}
		};

	var arrShapes = [lineCurPos];
	var arrAnnotations = [];

	$.each(proteinsPositions, function (k, item) {

		var block = {
				type: 'rect',
				xref: 'x',
				yref: 'paper',
				x0: item.pos1,
				y0: 0,
				x1: item.pos2,
				y1: 1,
				fillcolor: randomColors[k],
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
			font: { color:randomColors[k] },
			arrowcolor: randomColors[k],
      arrowhead: 2,
      ax: 0,
      ay: -30
    };

		arrAnnotations.push(annotation);

	});

	var layout = {
		autosize: true,
		//title: 'Click the desired position',							
		xaxis: {
			title: "Number of base pair",
			showline: true,
			showgrid: false,
			zeroline: false
		},
		yaxis: {
			title: "",
			showline: false,
			showgrid: false,
			zeroline: false,
			autorange:true,
			showticklabels: false
		},
		showlegend: false,
		shapes: arrShapes,
		annotations: arrAnnotations
	};

	Plotly.newPlot('blockPlotDiv', data, layout).then(function(){
		$("#loading-block").hide();
	});

}

window.addEventListener('resize', function(){
	if($("#blockPlotDiv").length && $('#blockPlotDiv').is(':visible')) {
		blockPlotDiv.layout.width = $("#blockPlotDiv").width();
		Plotly.redraw(blockPlotDiv);
	}
	if($("#affinityPlotDiv").length) {
		affinityPlotDiv.layout.width = $("#affinityPlotDiv").width();
		Plotly.redraw(affinityPlotDiv);
	}
}, true);

jQuery(document).ready(function() {

		var html_res = $(".resolution2 option[value='']")[0].outerHTML + $(".resolution2 option[value='0']")[0].outerHTML + $(".resolution2 option[value='1']")[0].outerHTML;

		loadProteinFields();

		/*$('select.selectpicker').selectpicker({
			caretIcon: 'glyphicon glyphicon-menu-down',
			noneSelectedText: '&nbsp;'	
		});*/

		$('.tools2').on('change', function() {

			$(".fake-prots").hide();
			disableFakeProts();

			if($(this).val() == 4) {

				$('#fg-np').show();
				$('#nuclpos').prop('disabled', false);

				$(".resolution2 option[value='']").remove();
				$(".resolution2 option[value='1']").remove();

				$('#proteins-list').hide();
				disableProts();

				$('#dln-block').hide();
				$('.deltaLN2').prop('disabled', true);

				$('#fg_IterStruct2').hide();
				$('.iterStruct2').prop('disabled', true);
				
			} else if($(this).val() == 3) {

				$('#proteins-list').show();
				$(".mt-repeater").show();
				$('.protein-id').each(function() { 
					$(this).prop('disabled', false);
				});
				
				$(".resolution2").html(html_res);

				$('#fg-np').hide();
				$('#nuclpos').prop('disabled', true);

				$('#dln-block').hide();
				$('.deltaLN2').prop('disabled', true);

				$('#fg_IterStruct2').hide();
				$('.iterStruct2').prop('disabled', true);

			} else if($(this).val() == 2) {

				$('#proteins-list').hide();
				disableProts();

				$(".resolution2").html(html_res);

				$('#fg-np').hide();
				$('#nuclpos').prop('disabled', true);

				$('#dln-block').show();
				$('.deltaLN2').prop('disabled', false);

				$('#fg_IterStruct2').show();
				$('.iterStruct2').prop('disabled', false);

			} else {

				$('#proteins-list').hide();
				disableProts();

				$(".resolution2").html(html_res);

				$('#fg-np').hide();
				$('#nuclpos').prop('disabled', true);

				$('#dln-block').hide();
				$('.deltaLN2').prop('disabled', true);

				$('#fg_IterStruct2').hide();
				$('.iterStruct2').prop('disabled', true);

			}

		});

		$("#seqtxt").on("change keyup paste", function() {
	
			//$("#seqprots").html($("#seqtxt").val());

		});

		$("#add-sample-seq").on("click", function() {
		
			$("#seqtxt").val($("#sample-seq").val());

		});

		$("#add-sample-np").on("click", function() {
		
			$("#nuclpos").val($("#sample-np").val());

		});
		
		$("#add-sample-prot").on("click", function() {
		
			$(".mt-repeater").hide();
			$(".fake-prots").show();
			
			disableProts();
			enableFakeProts();			

		});	

		$('#modalAffinity').on('shown.bs.modal', function (e) {

			

			$.ajax({
					type: "GET",
					url: baseURL + "affinity",
					data: "seq=" + affinityParams[0].toUpperCase() + "&prot=" + affinityParams[1] + "&pos=" + affinityParams[2],
					contentType: false,
					processData: false,
					success: function (output) {

						figure = JSON.parse(output);

						drawPlot(figure);

					}
			});

					
		});

		$('#modalErrProts').on('shown.bs.modal', function (e) {

			if($('#loading-block').is(':visible')) {
				$('#eos-legend').show();
				drawPlotProteins();
			}

		});

		$('#modalErrProts').on('hidden.bs.modal', function (e) {

			$('#eos-legend').hide();

		});

		$('#modalAffinity').on('hidden.bs.modal', function (e) {

			$("#shape-legend").hide();

		});

		/*$('.protein-position').each(function() {
			
				$(this).on('change', function() {
		
					console.log($(this).val());

				});

			});*/

		FormRepeater.init();
		FormValidation.init();
		Select2Init.init();

		// SAMPLE INPUT
		
		switch(sampleType) {
		
			// MCDNA - CG
			case '1':
				$( "#add-sample-seq" ).trigger( "click");
				$('.tools2').val("1").trigger('change');
				$('.resolution2').val("0").trigger('change');
				$('#operations2').val(["createStructure","createTrajectory"]).trigger('change');
				break;

			// MCDNA - AA
			case '2':
				$( "#add-sample-seq" ).trigger( "click");
				$('.tools2').val("1").trigger('change');
				$('.resolution2').val("1").trigger('change');
				$('#operations2').val(["createStructure","createTrajectory"]).trigger('change');
				break;

			// Circular - CG
			case '3':
				$( "#add-sample-seq" ).trigger( "click");
				$('.tools2').val("2").trigger('change');
				$('.resolution2').val("0").trigger('change');
				$('#operations2').val(["createStructure","createTrajectory"]).trigger('change');
				break;

			// Circular - AA
			case '4':
				$( "#add-sample-seq" ).trigger( "click");
				$('.tools2').val("2").trigger('change');
				$('.resolution2').val("1").trigger('change');
				$('#operations2').val(["createStructure","createTrajectory"]).trigger('change');
				break;

			// Proteins - CG
			case '5':
				$( "#add-sample-seq" ).trigger( "click");
				$('.tools2').val("3").trigger('change');
				$('.resolution2').val("0").trigger('change');
				$( "#add-sample-prot" ).trigger( "click");
				$('#operations2').val(["createStructure","createTrajectory"]).trigger('change');
				break;

			// Proteins - AA
			case '6':
				$( "#add-sample-seq" ).trigger( "click");
				$('.tools2').val("3").trigger('change');
				$('.resolution2').val("1").trigger('change');
				$( "#add-sample-prot" ).trigger( "click");
				$('#operations2').val(["createStructure","createTrajectory"]).trigger('change');
				break;

		}

});
