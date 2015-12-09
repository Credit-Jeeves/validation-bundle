/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
function ReLoader(url, redirect, failedLink) {
    var timeoutId = false;
    var nDelay = 3000; // 3 seconds;
    var limitRetries = 0;

    function checkStatus() {
        clearTimeout(timeoutId);
        jQuery.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            error: function () {
                window.location.href = failedLink;
                return false;
            },
            success: function (status) {
                limitRetries++;
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
                } else if (limitRetries > 10) {
                    window.location.href = failedLink;
                    return false;
                } else if ('processing' != status && 'warning' != status ) {
                    console.log('Fail');
                    return false;
                }

                timeoutId = setTimeout(checkStatus, nDelay);

                return false;
            }
        });
    }

    jQuery(function () {
        timeoutId = setTimeout(checkStatus, nDelay);
    });
}
