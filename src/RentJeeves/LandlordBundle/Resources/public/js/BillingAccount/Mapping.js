function Mapping() {
    this.billingAccount = {
        create: function(options) {
            return ko.observable(new BillingAccount(options.data));
        }
    };
}
