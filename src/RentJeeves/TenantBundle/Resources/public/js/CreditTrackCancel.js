function CreditTrackCancel() {
    ko.cleanNode(jQuery('#credit-track-cancel').get(0));
    this.cancelClose = function() {
        jQuery("#credit-track-cancel").dialog('close');
    };

    this.cancelUrl = ko.computed(function() {
        return Routing.generate('credittrack_cancel');
    }, self);
    jQuery("#credit-track-cancel").dialog({
        autoOpen: true,
        width: 660,
        modal: true
    });
    ko.applyBindings(this, jQuery('#credit-track-cancel').get(0));
}
