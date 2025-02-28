/**************************************/
/*           DOWNLOAD FILES           */
/**************************************/

function downloadFile(projectID, type) {

	location.href = baseURL + "download/file/" + type + "/" + projectID;

}

var CookieConsent = function () {

    var _acceptCookies = function () {
        document.cookie = "cookie_consent=accepted; path=/; max-age=31536000";
        location.reload(); // Reload to apply cookie consent
    }

    var _declineCookies = function () {
        document.cookie = "cookie_consent=declined; path=/; max-age=31536000";
        document.getElementById('cookie-banner').style.display = 'none';
    }

    return {
        acceptCookies: function () {
            _acceptCookies();
        },
        declineCookies: function () {
            _declineCookies();
        }
    };

}();

