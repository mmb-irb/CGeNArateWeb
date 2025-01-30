/**************************************/
/*           DOWNLOAD FILES           */
/**************************************/

function downloadFile(projectID, type) {

	location.href = baseURL + "download/file/" + type + "/" + projectID;

}

var CookieConsent = function () {

    var _init = function () {
        $('.mt-cookie-consent-bar').cookieBar({ 
            closeButton : '.mt-cookie-consent-btn' 
        });
    };

    return {
        init: function () {
            _init();
        }
    };

}();

$(document).ready(function() {    
		CookieConsent.init(); 
});

