function ResolveLate(data) {
    var self = this;

    this.resolveValue = ko.observable('email');
    this.amount = ko.observable();
    this.paidForOptions = ko.observableArray(null);
    this.paidFor = ko.observable(null);

    self.amount(data.amount);
    self.paidForOptions(associativeArrayToOptions(paidForArr));

    this.resolve = function() {
        $.ajax({
            url: Routing.generate('landlord_conflict_resolve'),
            type: 'POST',
            dataType: 'json',
            data: {
                'contract_id' : self.details().id,
                'amount': self.amount(),
                'action' : self.resolveValue()
            },
            success: function() {
                ActionsViewModel.ajaxAction();
                PaymentsViewModel.ajaxAction();
                self.closeForm();
            }
        });
    };
};
