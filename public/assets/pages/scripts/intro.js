var baseURL = $("#base-url").val();

// INITIAL INTRO
// home
function IntroHome() {
	
	$('#startButton').click( function() {
		introJs().setOptions({
			'doneLabel': 'See Input Form',
			'nextLabel': '>',
			'prevLabel': '<',
			steps: [
				{ 
					intro: "CGeNArate obtains a set of representative DNA conformations at base pair step level accuracy."
				},
				{ 
					intro: "In this short tour, users will see generic inputs and outputs. All the sample inputs and outputs have its own guided tour."
				},
				{
					element: document.querySelectorAll('[data-step="1"]')[0],
					intro: "Use the top menu to navigate around the website.",
				},
			]
		}).start().oncomplete(function() {
			window.location.href = baseURL + 'input?intro1=true';
		});
	});

	if (RegExp('intro1', 'gi').test(window.location.search)) {
		introJs().setOptions({
			'doneLabel': 'See Project Output',
			'nextLabel': '>',
			'prevLabel': '<',
			steps: [
				{
					element: document.querySelectorAll('[data-step="1"]')[0],
					intro: "Use this menu to access the sample inputs."
				},
				{
					element: document.querySelectorAll('[data-step="2"]')[0],
					intro: "Fill the form choosing tool, resolution, operations ans so on."
				},
				{
					element: document.querySelectorAll('[data-step="3"]')[0],
					intro: "Once the form is filled, click submit and wait until the job has finished."
				}
					
			]
		}).start().oncomplete(function() {
			window.location.href = baseURL + 'output/summary/' + sample1 + '/sample?intro2=true';
		});
	}

	if (RegExp('intro2', 'gi').test(window.location.search)) {
		introJs().setOptions({
			'doneLabel': 'End tour',
			'nextLabel': '>',
			'prevLabel': '<',
			steps: [
				{
					intro: "Once the job has finished, the results are shown."
				},
				{
					element: document.querySelectorAll('[data-step="1"]')[0],
					intro: "The summary menu allows users to access summary and analysis (if they have been selected in the input form)."
				},
				{
					element: document.querySelectorAll('[data-step="2"]')[0],
					intro: "The inputs summary are shown in this box."
				},
				{
					element: document.querySelectorAll('[data-step="3"]')[0],
					intro: "If the user has selected <em>Create Structure</em> as operation, the 3D structure is shown in this box."
				},
				{
					element: document.querySelectorAll('[data-step="4"]')[0],
					intro: "If the user has selected <em>Create Trajectory</em> as operation, the 3D trajectory is shown in this box."
				}
			]
		}).start();
	}
}

// INPUTS
// input mcdna
function IntroInputMCDNA() {
	$('#inputsMCDNA').click( function() {
		introJs().setOptions({
			'nextLabel': '>',
			'prevLabel': '<',
			steps: [
				{
					element: document.querySelectorAll('[data-step="20"]')[0],
					intro: "All the form fields have a help button that shows a tooltip with a short text explaining the meaning of the field.",
				},
				{
					element: document.querySelectorAll('[data-step="21"]')[0],
					intro: "Insert the DNA sequence in this text field.",
				},
				{
					element: document.querySelectorAll('[data-step="22"]')[0],
					intro: "Select the tool in this selectable field.",
				},
				{
					element: document.querySelectorAll('[data-step="23"]')[0],
					intro: "Select the resolution in this selectable field.",
				},
				{
					element: document.querySelectorAll('[data-step="28"]')[0],
					intro: "Select the operations you want to execute. Note that <em>Create trajectory</em> implies more calculating time.",
				},
				{
					element: document.querySelectorAll('[data-step="29"]')[0],
					intro: "Insert the number of structures (in case you have selected <em>Create trajectory</em>) in this text field.",
				},
				{
					element: document.querySelectorAll('[data-step="30"]')[0],
					intro: "In case you want to receive an email once the job is done, fill this field.",
				},
				{
					element: document.querySelectorAll('[data-step="31"]')[0],
					intro: "Select or unselect the anaylisis checkbox. Note that selecting the analysis implies more calculating time.",
				},
				{
					element: document.querySelectorAll('[data-step="3"]')[0],
					intro: "Once the form is completely full, click submit to launch the job.",
				},				
			]
		}).start();
	});
}

// input circular
function IntroInputCircular() {
	$('#inputsCircular').click( function() {
		introJs().setOptions({
			'nextLabel': '>',
			'prevLabel': '<',
			steps: [
				{
					element: document.querySelectorAll('[data-step="20"]')[0],
					intro: "All the form fields have a help button that shows a tooltip with a short text explaining the meaning of the field.",
				},
				{
					element: document.querySelectorAll('[data-step="21"]')[0],
					intro: "Insert the DNA sequence in this text field.",
				},
				{
					element: document.querySelectorAll('[data-step="22"]')[0],
					intro: "Select the tool in this selectable field.",
				},
				{
					element: document.querySelectorAll('[data-step="23"]')[0],
					intro: "Select the resolution in this selectable field.",
				},
				{
					element: document.querySelectorAll('[data-step="24"]')[0],
					intro: "Insert the Delta Linking Number in this numeric field.",
				},
				{
					element: document.querySelectorAll('[data-step="25"]')[0],
					intro: "Insert the number of iterations per structure in this numeric field. Note that the higher this value is, more calculating time will be taken.",
				},
				{
					element: document.querySelectorAll('[data-step="28"]')[0],
					intro: "Select the operations you want to execute. Note that <em>Create trajectory</em> implies more calculating time.",
				},
				{
					element: document.querySelectorAll('[data-step="29"]')[0],
					intro: "Insert the number of structures (in case you have selected <em>Create trajectory</em>) in this text field.",
				},
				{
					element: document.querySelectorAll('[data-step="30"]')[0],
					intro: "In case you want to receive an email once the job is done, fill this field.",
				},
				{
					element: document.querySelectorAll('[data-step="31"]')[0],
					intro: "Select or unselect the anaylisis checkbox. Note that selecting the analysis implies more calculating time.",
				},
				{
					element: document.querySelectorAll('[data-step="3"]')[0],
					intro: "Once the form is completely full, click submit to launch the job.",
				},				
			]
		}).start();
	});
}

// input proteins
function IntroInputProteins() {
	$('#inputsProteins').click( function() {
		introJs().setOptions({
			'nextLabel': '>',
			'prevLabel': '<',
			steps: [
				{
					element: document.querySelectorAll('[data-step="20"]')[0],
					intro: "All the form fields have a help button that shows a tooltip with a short text explaining the meaning of the field.",
				},
				{
					element: document.querySelectorAll('[data-step="21"]')[0],
					intro: "Insert the DNA sequence in this text field.",
				},
				{
					element: document.querySelectorAll('[data-step="22"]')[0],
					intro: "Select the tool in this selectable field.",
				},
				{
					element: document.querySelectorAll('[data-step="23"]')[0],
					intro: "Select the resolution in this selectable field.",
				},
				{
					element: document.querySelectorAll('[data-step="26"]')[0],
					intro: "Select the proteins you will bound to the DNA sequence.",
				},
				{
					element: document.querySelectorAll('[data-step="27"]')[0],
					intro: "In order to put an optimal protein position, please click this button and an affinity plot will be shown.",
				},
				{
					element: document.querySelectorAll('[data-step="28"]')[0],
					intro: "Select the operations you want to execute. Note that <em>Create trajectory</em> implies more calculating time.",
				},
				{
					element: document.querySelectorAll('[data-step="29"]')[0],
					intro: "Insert the number of structures (in case you have selected <em>Create trajectory</em>) in this text field.",
				},
				{
					element: document.querySelectorAll('[data-step="30"]')[0],
					intro: "In case you want to receive an email once the job is done, fill this field.",
				},
				{
					element: document.querySelectorAll('[data-step="31"]')[0],
					intro: "Select or unselect the anaylisis checkbox. Note that selecting the analysis implies more calculating time.",
				},
				{
					element: document.querySelectorAll('[data-step="3"]')[0],
					intro: "Once the form is completely full, click submit to launch the job.",
				},				
			]
		}).start();
	});
}

// contacts mcdna
function IntroContactsMCDNA() {
	$('#contactsMCDNA').click( function() {

		if($("#strType").val() == "eq") {
			var st = [
				{ 
					intro: "Distances for all pairs of nucleotides (all against all) in the sequence."
				},
				{
					element: document.querySelectorAll('[data-step="2"]')[0],
					intro: "Contact Heatmap Plot Nucleotide - Nucleotide.",
				},
				
			];
		}else{
			var st = [
				{ 
					intro: "Distances for all pairs of nucleotides (all against all) in the sequence."
				},
				{
					element: document.querySelectorAll('[data-step="1"]')[0],
					intro: "Types of contacts.",
				},
				{
					element: document.querySelectorAll('[data-step="2"]')[0],
					intro: "Contact Heatmap Plot Nucleotide - Nucleotide.",
				},
				
			];
		}

		introJs().setOptions({
			'nextLabel': '>',
			'prevLabel': '<',
			steps: st
		}).start();
	});
}

// contacts proteins
function IntroContactsProteins() {
	$('#contactsProt').click( function() {
		if($("#strType").val() == "eq") {
			var st = [
				{ 
					intro: "Distances for all pairs of nucleotides (all against all) in the sequence, between the selected protein and the nucleotide and between two selected proteins."
				},
				{
					element: document.querySelectorAll('[data-step="2"]')[0],
					intro: "Contact Heatmap Plot Nucleotide - Nucleotide.",
				},
				{
					element: document.querySelectorAll('[data-step="3"]')[0],
					intro: "Types of contact plots.",
				},
				{
					element: document.querySelectorAll('[data-step="4"]')[0],
					intro: "Clicking this button, we change to Protein-Nucleotide.",
				},
				{
					element: document.querySelectorAll('[data-step="5"]')[0],
					intro: "3D View of sequence.",
				},
				{
					element: document.querySelectorAll('[data-step="6"]')[0],
					intro: "Select the protein with which you want to visualize contacts with the nucleotide.",
				},
				{
					element: document.querySelectorAll('[data-step="7"]')[0],
					intro: "Clicking the <em>Load Contacts</em> button will load the heatmap.",
				},
				{
					element: document.querySelectorAll('[data-step="8"]')[0],
					intro: "Contact Heatmap Plot Protein - Nucleotide.",
				},
				{
					element: document.querySelectorAll('[data-step="9"]')[0],
					intro: "Clicking this button, we change to Protein-Protein.",
				},
				{
					element: document.querySelectorAll('[data-step="10"]')[0],
					intro: "3D View of sequence.",
				},
				{
					element: document.querySelectorAll('[data-step="11"]')[0],
					intro: "Select the <strong>two</strong> proteins with which you want to visualize contacts.",
				},
				{
					element: document.querySelectorAll('[data-step="12"]')[0],
					intro: "Clicking the <em>Load Contacts</em> button will load the heatmap.",
				},
				{
					element: document.querySelectorAll('[data-step="13"]')[0],
					intro: "Contact Heatmap Plot Protein - Protein.",
				},
			];
		}else{
			var st = [
				{ 
					intro: "Distances for all pairs of nucleotides (all against all) in the sequence, between the selected protein and the nucleotide and between two selected proteins."
				},
				{
					element: document.querySelectorAll('[data-step="1"]')[0],
					intro: "Types of contacts.",
				},
				{
					element: document.querySelectorAll('[data-step="2"]')[0],
					intro: "Contact Heatmap Plot Nucleotide - Nucleotide.",
				},
				{
					element: document.querySelectorAll('[data-step="3"]')[0],
					intro: "Types of contact plots.",
				},
				{
					element: document.querySelectorAll('[data-step="4"]')[0],
					intro: "Clicking this button, we change to Protein-Nucleotide.",
				},
				{
					element: document.querySelectorAll('[data-step="5"]')[0],
					intro: "3D View of sequence.",
				},
				{
					element: document.querySelectorAll('[data-step="6"]')[0],
					intro: "Select the protein with which you want to visualize contacts with the nucleotide.",
				},
				{
					element: document.querySelectorAll('[data-step="7"]')[0],
					intro: "Clicking the <em>Load Contacts</em> button will load the heatmap.",
				},
				{
					element: document.querySelectorAll('[data-step="8"]')[0],
					intro: "Contact Heatmap Plot Protein - Nucleotide.",
				},
				{
					element: document.querySelectorAll('[data-step="9"]')[0],
					intro: "Clicking this button, we change to Protein-Protein.",
				},
				{
					element: document.querySelectorAll('[data-step="10"]')[0],
					intro: "3D View of sequence.",
				},
				{
					element: document.querySelectorAll('[data-step="11"]')[0],
					intro: "Select the <strong>two</strong> proteins with which you want to visualize contacts.",
				},
				{
					element: document.querySelectorAll('[data-step="12"]')[0],
					intro: "Clicking the <em>Load Contacts</em> button will load the heatmap.",
				},
				{
					element: document.querySelectorAll('[data-step="13"]')[0],
					intro: "Contact Heatmap Plot Protein - Protein.",
				},
			];
		}

		var ijs = introJs().setOptions({
			'nextLabel': '>',
			'prevLabel': '<',
			steps: st 
		}).start().onchange(function(targetElement) {
			var step = $(targetElement).data("step");

			if(step == "3") {
				$("#bt-NUC-NUC")[0].click();
			}

			if(step == "4") {
				$("#bt-PROT-NUC")[0].click();
			}

			if(step == "6") {
				$('input[name="proteinIDradio"]')[0].click();
			}

			if(step == "8") {
				$('#submit-prot-nuc')[0].click();
			}

			/*if(step == "8") {
				$("#bt-PROT-NUC")[0].click();
			}*/

			if(step == "9") {
				$("#bt-PROT-PROT")[0].click();
			}

			if(step == "11") {
				$('input[name="proteinIDcheckbox"]')[0].click();
				$('input[name="proteinIDcheckbox"]')[1].click();
			}

			if(step == "13") {
				$('#submit-prot-prot')[0].click();
			}

		});
	});
}

// bending
function IntroBending() {
	$('#bendingTour').click( function() {
		if($("#strType").val() == "eq") {
			var st = [
				{ 
					intro: "The bending of the DNA fiber is calculated based on the values of the rotational base-pair step parameters tilt, roll and twist."
				},
				{
					element: document.querySelectorAll('[data-step="1"]')[0],
					intro: "Table of values for bending of the whole DNA fiber.",
				},
				{
					element: document.querySelectorAll('[data-step="2"]')[0],
					intro: "Bending distribution of 5/10 bp segments.",
				},
				{
					element: document.querySelectorAll('[data-step="3"]')[0],
					intro: "Bending of 5/10 bp segments in xz/yz direction along sequence.",
				},
			];
		}else{
			var st = [
				{ 
					intro: "The bending of the DNA fiber is calculated based on the values of the rotational base-pair step parameters tilt, roll and twist."
				},
				{
					element: document.querySelectorAll('[data-step="4"]')[0],
					intro: "Bending distribution of 5/10 bp segments and the whole fiber.",
				},
				{
					element: document.querySelectorAll('[data-step="5"]')[0],
					intro: "Bending of 5/10 bp segments in xz/yz direction along sequence.",
				},
				{
					element: document.querySelectorAll('[data-step="6"]')[0],
					intro: "Distribution of contributions of xz- and yz-bending to total bending of whole fiber.",
				},
				{
					element: document.querySelectorAll('[data-step="7"]')[0],
					intro: "Bending of whole fiber along trajectory.",
				},
			];
		}

		introJs().setOptions({
			'nextLabel': '>',
			'prevLabel': '<',
			steps: st
		}).start();
	});
}

// pcazip
function IntroPCAZip() {
	$('#pcazipTour').click( function() {
		introJs().setOptions({
			'nextLabel': '>',
			'prevLabel': '<',
			steps: [
				{ 
					intro: "The Principal Component Analysis graphical interface offers the possibility of studying the real movements of the structure through the projections of the trajectory onto the different essential modes."
				},
				{
					element: document.querySelectorAll('[data-step="1"]')[0],
					intro: "Table showing the first 10 principal components.",
				},
				{
					element: document.querySelectorAll('[data-step="2"]')[0],
					intro: "3D structure of each of the principal components.",
				},
				{
					element: document.querySelectorAll('[data-step="3"]')[0],
					intro: "Plot containing the displacement (in Angstroms) of the projections of all the trajectory snapshots into the associated eigenvector.",
				},
			]
		}).start();
	});
}

// end-to-end
function IntroEndToEnd() {
	$('#endtoendTour').click( function() {
		introJs().setOptions({
			'nextLabel': '>',
			'prevLabel': '<',
			steps: [
				{ 
					intro: "The end to end distance of the DNA fiber is calculated as the distance of the C5 atom of the first and last base of the 3’ -> 5’ strand."
				},
				{
					element: document.querySelectorAll('[data-step="1"]')[0],
					intro: "Trajectory snapshots sorted by distance. Interacts with the plot and the 3D structure.",
				},
				{
					element: document.querySelectorAll('[data-step="2"]')[0],
					intro: "Distances / Snapshots plot. Interacts with the slider and the 3D structure.",
				},
				{
					element: document.querySelectorAll('[data-step="3"]')[0],
					intro: "3D structure corresponding to the distance selected.",
				},
			]
		}).start();
	});
}

// curves
function IntroCurves() {
	$('#curvesTour').click( function() {
		if($("#strType").val() == "eq") {
			var st = [
				{ 
					intro: "Using Curves+, CGeNArate offers a complete study of Nucleic Acids Helical Parameters."
				},
				{
					element: document.querySelectorAll('[data-step="1"]')[0],
					intro: "Using this menu, users can switch studies.",
				},
				{
					element: document.querySelectorAll('[data-step="2"]')[0],
					intro: "Using this menu, users can switch elements of flexibility.",
				},
				{
					element: document.querySelectorAll('[data-step="3"]')[0],
					intro: "Plot related to element of flexibility selected.",
				},
			];
		}else{
			var st = [
				{ 
					intro: "Using Curves+, CGeNArate offers a complete study of Nucleic Acids Helical Parameters."
				},
				{
					element: document.querySelectorAll('[data-step="1"]')[0],
					intro: "Using this menu, users can switch studies.",
				},
				{
					element: document.querySelectorAll('[data-step="4"]')[0],
					intro: "Using this menu, users can switch elements of flexibility.",
				},
				{
					element: document.querySelectorAll('[data-step="5"]')[0],
					intro: "Plot related to element of flexibility selected.",
				},
			];
		}
		introJs().setOptions({
			'nextLabel': '>',
			'prevLabel': '<',
			steps: st 
		}).start();
	});
}

// stiffness
function IntroStiffness() {
	$('#stiffnessTour').click( function() {
		introJs().setOptions({
			'nextLabel': '>',
			'prevLabel': '<',
			steps: [
				{ 
					intro: "Elastic force constants associated with helical deformation at the base pair step level were determined by inversion of the covariance matrix in helical space, which yields stiffness matrices whose diagonal elements provide the stiffness constants."
				},
				{
					element: document.querySelectorAll('[data-step="1"]')[0],
					intro: "Using this menu, users can switch elements of flexibility.",
				},
				{
					element: document.querySelectorAll('[data-step="2"]')[0],
					intro: "Plot related to element of flexibility selected.",
				},
			]
		}).start();
	});
}

// circular
function IntroCircular() {
	$('#circularTour').click( function() {
		if($("#strType").val() == "eq") {
			var st = [
				{ 
					intro: "The analysis parameters for circular DNA are Twist, Writhe and Radius of gyration."
				},
				{
					element: document.querySelectorAll('[data-step="1"]')[0],
					intro: "The values of Twist, Writhe and Radius of gyration are given for the relaxed circular structure.",
				},
			];
		}else{
			var st = [
				{ 
					intro: "The analysis parameters for circular DNA are Twist, Writhe and Radius of gyration."
				},
				{
					element: document.querySelectorAll('[data-step="2"]')[0],
					intro: "Radius of gyration (in nm), Twist (in turns) and Writhe (in turns) are plotted against the index of the snapshot of the trajectory.",
				},
				{
					element: document.querySelectorAll('[data-step="3"]')[0],
					intro: "Plot related to element selected.",
				},
			];
		}
		introJs().setOptions({
			'nextLabel': '>',
			'prevLabel': '<',
			steps: st 
		}).start();
	});
}

// energy
function IntroEnergy() {
	$('#energyTour').click( function() {
		if($("#strType").val() == "eq") {
			if($("#tool").val() == "3") {
				var st = [
				{ 
					intro: "Elastic Energy of DNA."
				},
				{
					element: document.querySelectorAll('[data-step="1"]')[0],
					intro: "The table shows mean and standard deviation for the elastic energy of the DNA fiber as well as the average elastic energy per base pair.",
				},
				{
					element: document.querySelectorAll('[data-step="3"]')[0],
					intro: "Table with elastic energy and average elastic energy per base pair of the DNA not bound to a protein.",
				},
				{
					element: document.querySelectorAll('[data-step="5"]')[0],
					intro: "Table with the elastic energy of the protein-bound DNA is shown ordered according to the appearance along the DNA fiber.",
				},
				{
					element: document.querySelectorAll('[data-step="6"]')[0],
					intro: "Values of the elastic energy in kcal/mol (y-axis) along the bound DNA (x-axis).",
				},
			];
			} else {
				var st = [
					{ 
						intro: "Elastic Energy of DNA."
					},
					{
						element: document.querySelectorAll('[data-step="1"]')[0],
						intro: "The table shows mean and standard deviation for the elastic energy of the DNA fiber as well as the average elastic energy per base pair.",
					}
				];
			}
		}else{
			if($("#tool").val() == "3") {
				var st = [
					{ 
						intro: "Elastic Energy of DNA."
					},
					{
						element: document.querySelectorAll('[data-step="1"]')[0],
						intro: "The table shows mean and standard deviation for the elastic energy of the DNA fiber as well as the average elastic energy per base pair.",
					},
					{
						element: document.querySelectorAll('[data-step="2"]')[0],
						intro: "The elastic energy and the average elastic energy per base pair (y-axis, in kcal/mol) is shown for each snapshot (x-axis) of the trajectory.",
					},
					{
						element: document.querySelectorAll('[data-step="3"]')[0],
						intro: "Table with elastic energy and average elastic energy per base pair of the DNA not bound to a protein.",
					},
					{
						element: document.querySelectorAll('[data-step="4"]')[0],
						intro: "The elastic energy and the average elastic energy per base pair (y-axis, in kcal/mol) is shown for each snapshot (x-axis) of the trajectory..",
					},
					{
						element: document.querySelectorAll('[data-step="5"]')[0],
						intro: "Table with the elastic energy of the protein-bound DNA is shown ordered according to the appearance along the DNA fiber.",
					},
					{
						element: document.querySelectorAll('[data-step="6"]')[0],
						intro: "Values of the elastic energy in kcal/mol (y-axis) along the bound DNA (x-axis).",
					},
				];
			} else {
				var st = [
					{ 
						intro: "Elastic Energy of DNA."
					},
					{
						element: document.querySelectorAll('[data-step="1"]')[0],
						intro: "The table shows mean and standard deviation for the elastic energy of the DNA fiber as well as the average elastic energy per base pair.",
					},
					{
						element: document.querySelectorAll('[data-step="2"]')[0],
						intro: "The elastic energy and the average elastic energy per base pair (y-axis, in kcal/mol) is shown for each snapshot (x-axis) of the trajectory.",
					},
				];
			}
		}

		introJs().setOptions({
			'nextLabel': '>',
			'prevLabel': '<',
			steps: st
		}).start();
	});
}

// sasa
function IntroSASA() {
	$('#sasaTour').click( function() {
		if($("#strType").val() == "eq") {
			var st = [
				{ 
					intro: "Solvent-Accessible Surface Area Analysis."
				},
				{
					element: document.querySelectorAll('[data-step="1"]')[0],
					intro: "3D View of structure.",
				},
				{
					element: document.querySelectorAll('[data-step="3"]')[0],
					intro: "Plot with SASA values for every snapshot.",
				},
			];
		}else{
			var st = [
				{ 
					intro: "Solvent-Accessible Surface Area Analysis."
				},
				{
					element: document.querySelectorAll('[data-step="1"]')[0],
					intro: "3D View of structure.",
				},
				{
					element: document.querySelectorAll('[data-step="2"]')[0],
					intro: "Trajectory command box.",
				},
				{
					element: document.querySelectorAll('[data-step="3"]')[0],
					intro: "Plot with SASA values for every snapshot.",
				},
			];
		}
		introJs().setOptions({
			'nextLabel': '>',
			'prevLabel': '<',
			steps: st 
		}).start();
	});
}


$(document).ready(function() {

	// generic intro
	IntroHome();

	// ******
	// INPUTS
	// ******
	// input mcdna
	IntroInputMCDNA();
	// input circular
	IntroInputCircular();
	// input proteins
	IntroInputProteins();

	// *******
	// OUTPUTS
	// *******
	// CONTACTS
	// contacts mcdna & circular
	IntroContactsMCDNA();
	// contacts proteins
	IntroContactsProteins();
	// bending
	IntroBending();
	// pcazip
	IntroPCAZip();
	// end-to-end
	IntroEndToEnd();
	// curves
	IntroCurves();
	// stiffness
	IntroStiffness();
	// circular
	IntroCircular();
	// energy
	IntroEnergy();
	// sasa
	IntroSASA();

});
