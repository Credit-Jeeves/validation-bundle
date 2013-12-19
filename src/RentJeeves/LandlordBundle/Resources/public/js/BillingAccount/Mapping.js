this.Mapping = (function() {

    function Mapping() {
        var _this = this;
        this.billingAccount = {
            create: function(options) {
                return ko.observable(new BillingAccount(options.data));
            }
        };
    }

    return Mapping;

})();
