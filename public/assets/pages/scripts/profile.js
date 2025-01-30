var Profile = function () {

	var handleProfile = function() {

        $('#form-change-profile').validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            rules: {
            	name: {
    					required: true
      				},
      				surname: {
      					required: true
      				},
      				institution: {
      					required: true
      				},
            },

            invalidHandler: function(event, validator) { //display error alert on form submit
                //$('#err-mail-pwd', $('.login-form')).show();
            },

            highlight: function(element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').addClass('has-error'); // set error class to the control group
            },

            success: function(label) {
                label.closest('.form-group').removeClass('has-error');
                label.remove();
            },

            errorPlacement: function(error, element) {

				if (element.closest('.input-icon').size() === 1) {
                    error.insertAfter(element.closest('.input-icon'));
                } else {
                    error.insertAfter(element);
                }

            },

            submitHandler: function(form) {
             form.submit(); 
            }
        });

        $('#form-change-profile input').keypress(function(e) {
            if (e.which == 13) {
                if ($('#form-change-profile').validate().form()) {
                    $('#form-change-profile').submit(); //form validation success, call ajax form submit
                }
                return false;
            }
        });
    }


	var handlePassword = function() {

        $('#form-change-pwd').validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            rules: {
      				oldpassword: {
      					required: true
      				},
      				password1: {
                required: true
              },
              password2: {
                equalTo: "#password1"
              },

            },

            invalidHandler: function(event, validator) { //display error alert on form submit
                //$('#err-mail-pwd', $('.login-form')).show();
            },

            highlight: function(element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').addClass('has-error'); // set error class to the control group
            },

            success: function(label) {
                label.closest('.form-group').removeClass('has-error');
                label.remove();
            },

            errorPlacement: function(error, element) {

				    if (element.closest('.input-icon').size() === 1) {
                    error.insertAfter(element.closest('.input-icon'));
                } else {
                    error.insertAfter(element);
                }

            },

            submitHandler: function(form) {
               form.submit(); 
            }
        });

        $('#form-change-pwd input').keypress(function(e) {
            if (e.which == 13) {
                if ($('#form-change-pwd').validate().form()) {
                    $('#form-change-pwd').submit(); //form validation success, call ajax form submit
                }
                return false;
            }
        });
    }


    return {
        //main function to initiate the module
        init: function () {
        	handleProfile();
        	handlePassword();
        }

    };

}();

jQuery(document).ready(function() {
    Profile.init();
});


/*$('#submit-img').click(function(){
	var auxImg = $('.fileinput-preview.fileinput-exists.thumbnail img').attr('src');
	// fer les imatges i les inicials amb display-hide/display-show per posar i treure fÃ cil

    var formData = new FormData($('#form-chg-img')[0]);
    $.ajax({
        url: 'applib/uploadAvatar.php',  //Server script to process data
        type: 'POST',
        success: function(data){
			console.log('success: ' + data);
			d = data.replace(/(\r\n|\n|\r|\t)/gm,"");
			switch(d) {
				case '0':$('#err-chg-av').html('Error uploading file.')
						$('#err-chg-av').fadeIn(300);
						$('#succ-chg-av').fadeOut(300);
					   	break;
				case '1':$('#succ-chg-av').fadeIn(300);
						$('#err-chg-av').fadeOut(300);
						$(".img-responsive").attr("src", auxImg);
						$(".img-responsive").removeClass('display-hide');
						$("#avatar-usr-profile").hide();
						$("#avatar-with-picture").attr("src", auxImg);
						$("#avatar-with-picture").removeClass('display-hide');
						$("#avatar-no-picture").hide();
					   	break;
				case '2':$('#err-chg-av').html('Maximum size exceeded. Max allowed size 1MB.')
						$('#err-chg-av').fadeIn(300);
						$('#succ-chg-av').fadeOut(300);
					   	break;
				case '3':$('#err-chg-av').html('Invalid format. Please try with a png or jpg image.')
						$('#err-chg-av').fadeIn(300);
						$('#succ-chg-av').fadeOut(300);
					   	break;
				case '4':$('#err-chg-av').html('You must provide a file.')
						$('#err-chg-av').fadeIn(300);
						$('#succ-chg-av').fadeOut(300);
					   	break;
				case '5':$('#succ-chg-av').html('Profile picture successfully removed.')
						$('#succ-chg-av').fadeIn(300);
						$('#err-chg-av').fadeOut(300);
						$(".img-responsive").hide();
						$("#avatar-usr-profile").show()
						$("#avatar-with-picture").hide();
						$("#avatar-no-picture").show();
					   	break;


			}
		},
        error: function(data){
			console.log('error: ' + data);
		},
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    });
});*/
