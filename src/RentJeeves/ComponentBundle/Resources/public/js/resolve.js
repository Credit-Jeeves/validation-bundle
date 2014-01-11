function Resolve() {
    var self = this;
    this.details = ko.observable();
    this.errorsEnded = ko.observableArray([]);
    this.resolutionOptionsEnded = ko.observable("monthToMonth");
    this.outstandingBalance= ko.observable(0);
    this.endedContractDate = ko.observable();
    this.openForm = function(data) {
        self.details(data);
        if (data.status === 'CONTRACT ENDED') {
            self.openFormContractEnded(data);
            return;
        }

        $('#contract-resolve-late').dialog('open');
        $('#amount-paid').val(data.amount);

    };

    this.openFormContractEnded = function(data) {
        $('.datepicker').datepicker({
            showOn: "both",
            buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
            buttonImageOnly: true,
            showOtherMonths: true,
            selectOtherMonths: true,
            dateFormat: 'm/d/yy',
            minDate: new Date()
        });
        $('#contract-resolve-ended').dialog('open');
    };

    this.closeForm = function() {
        $('#contract-resolve-late').dialog('close');
    };

    this.closeFormEnded = function() {
        $('#contract-resolve-ended').dialog('close');
    };

    this.countErrorsEnded = function() {
        console.info(self.errorsEnded().length);
        return parseInt(self.errorsEnded().length);
    }

    this.resolveEnded = function() {
        jQuery('#contract-resolve-ended').showOverlay();
        switch (self.resolutionOptionsEnded()) {
            case 'monthToMonth':
                $.ajax({
                    url: Routing.generate('landlord_month_to_month', {'contractId': self.details().id }),
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        jQuery('#contract-resolve-ended').hideOverlay();
                        if (response.status !== 'successful') {
                            self.errorsEnded(response.errors)
                            return;
                        }

                        self.endedContractDate('');
                        self.outstandingBalance(0);
                        self.closeFormEnded();
                        ActionsViewModel.ajaxAction();
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
                            self.errorsEnded(response.errors)
                            return;
                        }

                        self.endedContractDate('');
                        self.outstandingBalance(0);
                        self.closeFormEnded();
                        ActionsViewModel.ajaxAction();
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
                        self.endedContractDate('');
                        self.outstandingBalance(0);
                        self.closeFormEnded();
                        ActionsViewModel.ajaxAction();
                    }
                });
                break;
        }
    };

    this.resolve = function() {
        $.ajax({
            url: Routing.generate('landlord_conflict_resolve'),
            type: 'POST',
            dataType: 'json',
            data: {
                'contract_id' : self.details().id,
                'amount': $('#amount-paid').val(),
                'action' : $('input[name=ResolutionOptions]:checked').attr('title')
            },
            success: function() {
                ActionsViewModel.ajaxAction();
                PaymentsViewModel.ajaxAction();
                self.closeForm();
            }
        });
    };
};
