function Cancel(id) {
    ko.cleanNode(jQuery('#payment-account-cancel').get(0));
    this.cancelClose = function() {
        jQuery("#payment-account-cancel").dialog('close');
    };

    this.cancelUrl = ko.computed(function() {
        return Routing.generate('checkout_pay_cancel', { id: id });
    }, self);
    jQuery("#payment-account-cancel").dialog({
        width:400,
        modal:true
    });
    ko.applyBindings(this, jQuery('#payment-account-cancel').get(0));
}
