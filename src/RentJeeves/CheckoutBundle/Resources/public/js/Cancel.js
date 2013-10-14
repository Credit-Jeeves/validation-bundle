function Cancel(id) {
    ko.cleanNode($('#payment-account-cancel').get(0));
    this.cancelClose = function() {
        $("#payment-account-cancel").dialog('close');
    };

    console.log(id);
    this.cancelUrl = ko.computed(function() {
        return Routing.generate('checkout_pay_cancel', { id: id });
    }, self);
    $("#payment-account-cancel").dialog({
        width:400,
        modal:true
    });
    ko.applyBindings(this, $('#payment-account-cancel').get(0));
}
