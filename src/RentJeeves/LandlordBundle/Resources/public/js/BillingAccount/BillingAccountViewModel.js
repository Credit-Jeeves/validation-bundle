this.BillingAccountViewModel = (function() {

    function AppViewModel(data) {
        this.billingAccounts = ko.observableArray([]);
        var mapping = new Mapping();
        ko.mapping.fromJS(data, mapping.billingAccount, this.billingAccounts);
        console.log(this.billingAccounts.length);
    }

    return AppViewModel;

})();
