function PaymentSources() {
    var self = this;
//    window.paymentAccounts

    this.id = ko.observable(null);
    this.delUrl = ko.computed(function() {
        return Routing.generate('tenant_payment_sources_del', { id: self.id() });
    });

    this.edit = function(id) {

    };
    this.delDialog = function(id) {
        self.id(id);
        $("#payment-account-delete").dialog({
            width:650,
            modal:true
        });
    };
    this.delClose = function(id) {
        $("#payment-account-delete").dialog('close');
    };
}
