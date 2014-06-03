/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
(function () {
    var self = this;

    var deleteCookie = function (name) {
        var date = new Date();
        date.setTime(date.getTime() + (-1 * 24 * 60 * 60 * 1000));
        document.cookie = name + "=; expires=" + date.toGMTString() + "; path=/";
    };

    this._lockApp = function () {
        deleteCookie(window.constants.get('SESSION_NAME'));
        deleteCookie(window.constants.get('SESSION_EXPIRATION_NAME'));

        window.location.reload();
    };

    this.timeout = function () {
        var sessionExpared = $.cookie(window.constants.get('SESSION_EXPIRATION_NAME'));
//        console.info(sessionExpared);
        if (sessionExpared) {
            // I don't understood this code, but it setup timezone on the client to 0
            // the same we have on the server - GMT.
            var d = new Date();
            var utc = d.getTime() + (d.getTimezoneOffset() * 60000);
            var now = new Date(utc);
            var date = new Date();
            var parsedDate = Date.parse(sessionExpared);
            if (!parsedDate) {
                return;
            }
            date.setTime(parsedDate);
//            console.info(date+ ' - parsed from server');
//            console.info(now+' - now on the browser');
            var left = date - now;
            if (window && window.console && window.console.log) {
                window.console.log('Session will expire in ' + (left / 1000) + ' seconds');
            }
            if (0 < left) {
                //console.info('timeout');
                setTimeout(self.timeout, left);
            } else {
                //console.info('lock app');
                self._lockApp();
            }
        }
    };

    if (typeof window.constants != 'undefined') {
        this.timeout();
    }
})();
