function ResolveEnded(parent, data) {
    ko.cleanNode(jQuery('#contract-resolve-ended').get(0));
    var self = this;
    this.details = ko.observable(data);
    this.errorsEnded = ko.observableArray([]);
    this.resolutionOptionsEnded = ko.observable("monthToMonth");
    this.outstandingBalance= ko.observable(0);
    this.endedContractDate = ko.observable("");
    this.isDialogVisible = ko.observable(true);

    this.countErrorsEnded = function() {
        return parseInt(self.errorsEnded().length);
    };

    this.resolveEnded = function() {
        jQuery('#contract-resolve-ended').showOverlay();
        switch (self.resolutionOptionsEnded()) {
            case 'monthToMonth':
                $.ajax({
                    url: Routing.generate('landlord_month_to_month', {'contractId': self.details().id }),
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        jQuery('#contract-resolve-ended').hideOverlay();
                        if (response.status !== 'successful') {
                            self.errorsEnded(response.errors);
                            return;
                        }
                        self.isDialogVisible(false);
                        parent.ajaxAction();
                    }
                });
                break;
            case 'changeDate':
                $.ajax({
                    url: Routing.generate('landlord_change_end_date_contract', {'contractId': self.details().id }),
                    type: 'POST',
                    data: {'finishAt': self.endedContractDate() },
                    dataType: 'json',
                    success: function (response) {
                        jQuery('#contract-resolve-ended').hideOverlay();
                        if (response.status !== 'successful') {
                            self.errorsEnded(response.errors);
                            return;
                        }
                        self.isDialogVisible(false);
                        parent.ajaxAction();
                    }
                });
                break;
            case 'markFinished':
                $.ajax({
                    url: Routing.generate('landlord_end_contract', {'contractId': self.details().id }),
                    type: 'POST',
                    data: {'uncollectedBalance': self.outstandingBalance() },
                    dataType: 'json',
                    success: function (response) {
                        jQuery('#contract-resolve-ended').hideOverlay();
                        self.isDialogVisible(false);
                        parent.ajaxAction();
                    }
                });
                break;
        }
    };


    ko.applyBindings(this, $('#contract-resolve-ended').get(0));
}
