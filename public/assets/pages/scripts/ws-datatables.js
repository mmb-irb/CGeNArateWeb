var TableDatatablesManaged = function () {

    var initTable = function () {

        // FUNCTION CONVERSION FILE SIZE
        jQuery.fn.dataTable.ext.type.order['file-size-pre'] = function ( data ) {

					if(!data) return 0;

          var matches = data.match( /^(\d+(?:\.\d+)?)\s*([a-z]+)/i );
          var multipliers = {
            b: 1,
            kb: 1000,
            mb: 1000000,
            gb: 1000000000,
            tb: 1000000000000,
            pb: 1000000000000000
          };

          var multiplier = multipliers[matches[2].toLowerCase()];
          return parseFloat( matches[1] ) * multiplier;
        };

        var table = $('#ws-table');

        // begin first table
        table.dataTable({

            // Internationalisation. For more info refer to http://datatables.net/manual/i18n
            "language": {
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                },
                "emptyTable": "No files in this folder, to change folder use the tree menu at left.",
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

            "bStateSave": true, // save datatable state(pagination, sort, etc) in cookie.

            "lengthMenu": [
                [5, 10, 20],
                [5, 10, 20] // change per page values here
            ],
            // set the initial value
            "pageLength": 5,
            "columnDefs": [
                {  // set default column settings
                    "orderable": false,
                    "targets": [0, 5]
                },
                {
                    "searchable": false,
                    "targets": [0]
                },
                {
                    "type": "file-size",
                    "targets": [4]
                },
                {
                    "className": "dt-right",
                    //"targets": [2]
                }
            ],
            "order": [
                [1, "asc"]
            ], // set first column as a default sort by asc
						"initComplete": function (settings, json) {
							$("#dynamic-table").trigger('heightTableChange');	
   					}
        });

        var tableWrapper = jQuery('#ws-table-wrapper');

        table.find('.group-checkable').change(function () {
          var checked = $(this).is(":checked");
	         $('input[type=checkbox]', table.fnGetNodes() ).prop('checked', checked);
        });

        table.on('change', 'tbody tr .checkboxes', function () {
            $(this).parents('tr').toggleClass("active");
        });
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


// we load datatables after loading jstree in ws-tree.js!!!!!
/*if (App.isAngularJsApp() === false) {
    jQuery(document).ready(function() {
        TableDatatablesManaged.init();
    });
}*/
