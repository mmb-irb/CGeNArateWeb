function previewNGL(id) {

	$('#viewport').html('');
  $('#modalNGL .modal-title').html('3D preview for ' + id.toUpperCase());
  $('#modalNGL').modal({ show: 'true' });

	$('#modalNGL').on('shown.bs.modal', function (e) {
				
		$('#viewport').html('');
		$("#loading-viewport").show();

		stage = new NGL.Stage( "viewport", { backgroundColor:"#c7ced1", tooltip:false } );
		stage.removeAllComponents();

		//stage.loadFile( "rcsb://" + id, { defaultRepresentation: false } )
		stage.loadFile( `https://files.rcsb.org/download/${id}.pdb`, { defaultRepresentation: false } )
		.then( function( o ){
			o.setSelection('/0');
			o.addRepresentation( "cartoon", { color: "sstruc", aspectRatio: 4, scale: 1	} );
			o.addRepresentation( "base", { sele: "*", color: "resname" } );
			o.addRepresentation( "licorice", { sele: "hetero and not(water or ion)", scale: 3, aspectRatio: 1.5	} );
			stage.autoView();
			$("#loading-viewport").hide();

		});

	});

}
