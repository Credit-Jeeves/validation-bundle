this.BillingAccount = (function() {

    function BillingAccount(data) {
        ko.mapping.fromJS(data);
    }

    return BillingAccount;

})();
