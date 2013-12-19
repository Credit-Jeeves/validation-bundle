this.BillingAccount = (function() {

    function BillingAccount(data) {
        ko.mapping.fromJS(data, {}, this);
    }

    return BillingAccount;

})();
