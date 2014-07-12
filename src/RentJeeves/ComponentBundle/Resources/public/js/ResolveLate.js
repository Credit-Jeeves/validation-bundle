function ResolveLate(parent, data) {
    ko.cleanNode(jQuery('#contract-resolve-late').get(0));
    var self = this;

    this.details = ko.observable(data);
    this.resolveValue = ko.observable('email');
    this.amount = ko.observable(data.amount);
    this.paidForOptions = ko.observableArray(associativeArrayToOptions(data.paidForArr));
    this.paidFor = ko.observable(null);

    this.isDialogVisible = ko.observable(true);
    this.isOverlayVisible = ko.observable(false);

    this.resolve = function() {
        jQuery('#contract-resolve-late').showOverlay();
        jQuery.ajax({
            url: Routing.generate('landlord_conflict_resolve'),
            type: 'POST',
            dataType: 'json',
            data: {
                'contract_id' : data.id,
                'amount': self.amount(),
                'action' : self.resolveValue(),
                'paid_for' : self.paidFor()
            },
            success: function() {
                parent.ajaxAction();
                PaymentsViewModel.ajaxAction(); //TODO do not use global values
                jQuery('#contract-resolve-late').hideOverlay();
                self.isDialogVisible(false);
            }
        });
    };


    ko.applyBindings(this, $('#contract-resolve-late').get(0));
}
