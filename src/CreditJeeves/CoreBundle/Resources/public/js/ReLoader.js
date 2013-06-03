/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
function ReLoader(url, redirect) {
    var timeoutId = false;
    var nDelay = 3000; // 3 seconds;

    function checkStatus() {
        clearTimeout(timeoutId);
        jQuery.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            success: function (status) {
                console.log(status);
                if (status && status.url) {
                    window.location.href = status.url;
                    return false;
                }
                if ('finished' == status) {
                    if (!redirect) {
                        location.reload();
                    } else {
                        window.location.href = redirect;
                        return true;
                    }
                    return false;
                } else if ('processing' != status && 'warning' != status ) {
                    return false;
                }
                timeoutId = setTimeout(checkStatus, nDelay);
            }
        });
    }

    jQuery(function () {
        timeoutId = setTimeout(checkStatus, nDelay);
    });
}
