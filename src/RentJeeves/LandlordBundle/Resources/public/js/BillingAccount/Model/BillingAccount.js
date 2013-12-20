var BillingAccount,
    __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

BillingAccount = (function() {

    function BillingAccount(data) {
        var defaults = {
            id: null,
            nickname: null,
            routingNumber: null,
            accountNumber: null,
            accountType: null,
            isActive: false
        }

        var data = $.extend(defaults, data);
        ko.mapping.fromJS(data, {}, this);
    }
    return BillingAccount;

})();
