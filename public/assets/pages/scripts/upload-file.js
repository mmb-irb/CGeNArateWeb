var baseURL = $('#base-url').val();	

var FormDropzone = function () {

  return {
    //main function to initiate the module
    init: function () {
      var count_added = 0;
			var count_uploaded = 0;
			Dropzone.options.myDropzone = {
        dictDefaultMessage: "Drop a PDB file here or click to upload",
        dictResponseError: "Error message",
				maxFilesize: "50",
				maxFiles: "1",
				acceptedFiles: ".pdb",
				success: function (file, response) {
					$("#err-upload").hide();

  				this.on("complete", function (file) {

						var r = JSON.parse(response);

						if(r.status == '1') {

							location.href = baseURL + "upload/select/ligand/" + r.dir;

						}else if(r.status == '2'){

							location.href = baseURL + "upload/step2/" + r.dir;

						}else{
								
							$("#err-upload span").html(r.msg);
							$("#err-upload").show();
							this.removeAllFiles(true);
							
						}

          });
  			},
				error: function (file, response){
					this.on("complete", function () {

						$("#err-upload span").html(response);
						$("#err-upload").show();
						this.removeAllFiles(true);

					});
				}
      }
    }
  };

}();

jQuery(document).ready(function() {
   FormDropzone.init();
});
