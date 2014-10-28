function ResolveLate(parent, data) {
    ko.cleanNode(jQuery('#contract-resolve-late').get(0));
    var self = this;

    this.details = ko.observable(data);
    this.resolveValue = ko.observable('email');
    this.amount = ko.observable(data.amount);
    this.paidForOptions = ko.observableArray(associativeArrayToOptions(data.paidForArr, null));
    this.paidFor = ko.observable(null);
    this.createdAt = ko.observable("");
    this.errorsEnded = ko.observableArray([]);

    this.isDialogVisible = ko.observable(true);
    this.isOverlayVisible = ko.observable(false);

    this.countErrorsEnded = function() {
        return parseInt(self.errorsEnded().length);
    };

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
                'paid_for' : self.paidFor(),
                'created_at': self.createdAt()
            },
            success: function(response) {
                jQuery('#contract-resolve-late').hideOverlay();
                if (response.status !== 'successful') {
                            self.errorsEnded(response.errors);
                            return;
                    }
                parent.ajaxAction();
                PaymentsViewModel.ajaxAction(); //TODO do not use global values
                self.isDialogVisible(false);
            }
        });
    };


    ko.applyBindings(this, $('#contract-resolve-late').get(0));
}
