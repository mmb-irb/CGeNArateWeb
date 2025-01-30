var FormValidationOut = function() {

    var handleValidationSignIn = function() {

        var form = $('#form-signin');
        var error = $('.alert-danger', form);

        form.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "", // validate all fields including form hidden input
            rules: {
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true
                }
            },

            invalidHandler: function(event, validator) { //display error alert on form submit
                error.show();
                App.scrollTo(error, -200);
            },

            errorPlacement: function(error, element) {
                error.insertAfter(element); // for other inputs, just perform default behavior
            },

            highlight: function(element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').addClass('has-error'); // set error class to the control group
            },

            unhighlight: function(element) { // revert the change done by hightlight
                $(element)
                    .closest('.form-group').removeClass('has-error'); // set error class to the control group
            },

            success: function(label) {
                label
                    .closest('.form-group').removeClass('has-error'); // set success class to the control group
            },

            submitHandler: function(form) {
                //error.hide();
							form.submit();	
            }
        });
    }

    var handleValidationForgotPassword = function() {

        var form = $('#form-forgot');
        var error = $('.alert-danger', form);
        var success = $('.alert-success', form);

        form.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "", // validate all fields including form hidden input
            rules: {
                email: {
                    required: true,
                    email: true
                }
            },

            invalidHandler: function(event, validator) { //display error alert on form submit
                success.hide();
                error.show();
                App.scrollTo(error, -200);
            },

            errorPlacement: function(error, element) {
                error.insertAfter(element); // for other inputs, just perform default behavior
            },

            highlight: function(element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').addClass('has-error'); // set error class to the control group
            },

            unhighlight: function(element) { // revert the change done by hightlight
                $(element)
                    .closest('.form-group').removeClass('has-error'); // set error class to the control group
            },

            success: function(label) {
                label
                    .closest('.form-group').removeClass('has-error'); // set success class to the control group
            },

            submitHandler: function(form) {
                /*success.show();
                error.hide();*/
								form.submit();	

            }
        });
    }

    var handleValidationSignUp = function() {

        var form = $('#form-signup');
        var error = $('.alert-danger', form);

        form.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "", // validate all fields including form hidden input
						messages : {
							password2: {
								equalTo: 'Both password fields must be equal.'
							}
						},
            rules: {
                name: {
                    required: true,
										//lettersonly: true,
                },
                surname: {
                    required: true,
										//lettersonly: true
                },
                institution: {
                    required: true
                },
                country: {
                    required: true
                },
                email: {
                    required: true,
                    email: true
                },
                password1: {
                    required: true,
										nowhitespace: true	
                },
                password2: {
                    equalTo: "#password1"
                }
            },

            invalidHandler: function(event, validator) { //display error alert on form submit
                error.show();
                App.scrollTo(error, -200);
            },

            errorPlacement: function(error, element) {
                error.insertAfter(element); // for other inputs, just perform default behavior
            },

            highlight: function(element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').addClass('has-error'); // set error class to the control group
            },

            unhighlight: function(element) { // revert the change done by hightlight
                $(element)
                    .closest('.form-group').removeClass('has-error'); // set error class to the control group
            },

            success: function(label) {
                label
                    .closest('.form-group').removeClass('has-error'); // set success class to the control group
            },

            submitHandler: function(form) {
                //error.hide();
								form.submit();	
            }
        });
    }

	var handleValidationResetPassword = function() {

        var form = $('#form-reset');
        var error = $('.alert-danger', form);

        form.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "", // validate all fields including form hidden input
						messages : {
							password2: {
								equalTo: 'Both password fields must be equal.'
							}
						},
            rules: {
              email: {
                  required: true,
                  email: true
              },
              password1: {
                  required: true
              },
              password2: {
                  equalTo: "#password1"
              }
            },

            invalidHandler: function(event, validator) { //display error alert on form submit
                error.show();
                App.scrollTo(error, -200);
            },

            errorPlacement: function(error, element) {
                error.insertAfter(element); // for other inputs, just perform default behavior
            },

            highlight: function(element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').addClass('has-error'); // set error class to the control group
            },

            unhighlight: function(element) { // revert the change done by hightlight
                $(element)
                    .closest('.form-group').removeClass('has-error'); // set error class to the control group
            },

            success: function(label) {
                label
                    .closest('.form-group').removeClass('has-error'); // set success class to the control group
            },

            submitHandler: function(form) {
                form.submit();	
            }

        });

    }	

    return {
        //main function to initiate the module
        init: function() {
            handleValidationSignIn();
            handleValidationForgotPassword();
            handleValidationSignUp();
						handleValidationResetPassword();
        }
    };
}();

jQuery(document).ready(function() {
    FormValidationOut.init();
});
