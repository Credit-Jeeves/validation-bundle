var BillingAccount;

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

        this.allowActive = !this.isActive();
    }

    return BillingAccount;

})();
