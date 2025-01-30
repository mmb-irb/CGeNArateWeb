var WSTree = function () {

		var baseURL = $('#base-url').val();	
		var userID = $('#user-id').val();	

    var handleTree = function () {
				
				function openDataTable(id) {
					
					$.ajax({
						
						type: "GET",
						url: baseURL + "dir/" + id,
						success: function(data) {
					
							$('#dynamic-table').removeClass('dtloading');	
							$('#dynamic-table').html(data);

							// load datatable after loading content
							TableDatatablesManaged.init();

							//console.log($('#dynamic-table').height());	


						}
			
					});

				}
			
				$.ajax({
					type: "GET",
					url: baseURL + "tree/" + userID,
					success: function(data) {

						//console.log(data);

						var input = JSON.parse(data);

						$('#tree').jstree({
							'plugins': ["types"],
							"types" : {
								"default" : {
										"icon" : "fa fa-folder icon-state-warning icon-lg"
								}
							},
							'core': {
								"themes" : {
										"responsive": false
								},
								'data': input.datatree
							}
						})
						.bind('ready.jstree', function (event, d) {

							if(Cookies.get('folder')) {

								//console.log(input.datatree.id,Cookies.get('folder') );
				
								if(!$(this).jstree('select_node', Cookies.get('folder'))){
	
									//$(this).jstree('select_node', input.uploadsID);
									$(this).jstree('select_node', input.datatree.id);
									$(this).jstree('open_node', input.datatree.id);

								} else {

									$(this).jstree('select_node', Cookies.get('folder'));
									$(this).jstree('open_node', Cookies.get('folder'));

								}

							}else{

								//$(this).jstree('select_node', input.uploadsID);
								$(this).jstree('select_node', input.datatree.id);
								$(this).jstree('open_node', input.datatree.id);

	
							}

						})
						.bind('select_node.jstree', function (event, d) {
							//if(d.node.id != "origin") {
								Cookies.set('folder', d.node.id);
								//$("#portlet-tree").trigger('heightChange');
								openDataTable(d.node.id);
							//}
						})
						.bind('after_open.jstree', function (event, d) {
							//$("#portlet-tree").trigger('heightTreeChange');	
						})
						.bind('after_close.jstree', function (event, d) {
							//$("#portlet-tree").trigger('heightTreeChange');	
						});
						
					}
				});

    }

    return {
        //main function to initiate the module
        init: function () {

            handleTree();

        }

    };

}();

if (App.isAngularJsApp() === false) {
    jQuery(document).ready(function() {
       WSTree.init();
    });
}
