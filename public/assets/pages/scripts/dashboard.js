var baseURL = $('#base-url').val();	

userRequest = function(id, role){
	
	$.ajax({
  	type: "GET",
    url: baseURL + "admin/change/type/" + id + "/" + role + "/100",
    success: function(d) {

			data = $.parseJSON(d);

			if(data.status == 1) {
				
				$('#' + id).remove();

				if($('.mt-action').length == 0) {
					$('.mt-actions').append('<div class="mt-action">No pending requests</div>');
				}

			}else{
					
				alert(data.msg);

			}

		}
	});

}

var Dashboard = function() {

    return {

			initKNOB: function () {
          //knob does not support ie8 so skip it
          if (!jQuery().knob || App.isIE8()) {
              return;
          }

          // general knob
          $(".knob").knob({
              'dynamicDraw': true,
              'thickness': 0.4,
              'tickColorizeValues': true,
              'skin': 'tron',
              'format' : function (value) {
                 return value + '%';
              },
              'draw': function() {
                $(this.i).css('font-size', '30px');
                $(this.i).css('font-weight', 'normal');
                $(this.i).css('font-family', '"Open Sans",sans-serif');
              }
          });
      },

			init: function() {

      	this.initKNOB();

			}

	};

}();


if (App.isAngularJsApp() === false) {
    jQuery(document).ready(function() {
        Dashboard.init(); // init metronic core componets
    });
}
