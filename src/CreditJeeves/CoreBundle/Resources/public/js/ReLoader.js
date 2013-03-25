/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
function ReLoader($url) {
    var timeoutId = false;
    var nDelay = 3000; // 3 seconds;

    function checkStatus() {
        clearTimeout(timeoutId);
        jQuery.ajax({
            url: $url,
            type: 'POST',
            dataType: 'json',
            success: function (status) {
                console.log(status);
                if (status && status.url) {
                    window.location.href = status.url;
                    return false;
                }
                if (status == 'finished') {
                    location.reload();
                    return false;
                }
            },
            complete: function () {
                timeoutId = setTimeout(checkStatus, nDelay);
            }
        });
    }

    jQuery(function () {
        timeoutId = setTimeout(checkStatus, nDelay);
    });
}
