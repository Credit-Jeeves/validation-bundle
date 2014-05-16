function TenantPaymentMapping() {
    this.tenantPayments = {
        create: function(options) {
            return ko.observable(new TenantPayment(options.data));
        }
    };
    this.pages = {
        create: function(options) {
            var result = [];
            for (var i = 1; i <= options.data; i++) {
                result[i] = i;
            }
            return result;
        }
    }
}

