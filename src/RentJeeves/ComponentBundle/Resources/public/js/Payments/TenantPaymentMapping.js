function TenantPaymentMapping() {
    this.payments = {
        create: function(options) {
            return ko.observable(new TenantPayment(options.data));
        }
    };
}

