//FIXME refactor. Make view model for each popup
function Resolve() {
    var self = this;
    this.details = ko.observable();
    this.errorsEnded = ko.observableArray([]);
    this.resolutionOptionsEnded = ko.observable("monthToMonth");
    this.outstandingBalance= ko.observable(0);
    this.endedContractDate = ko.observable();
    this.resolveValue = ko.observable('email');
    this.amount = ko.observable();
    this.paidForOptions = ko.observableArray(null);
    this.paidFor = ko.observable(null);
    this.openForm = function(data) {
        self.details(data);
        if (data.status === 'CONTRACT ENDED') {
            self.openFormContractEnded(data);
            return;
        }

        $('#contract-resolve-late').dialog('open'); // FIXME replace by knockoutJS dialog handler
        self.amount(data.amount);
        self.paidForOptions({}/*associativeArrayToOptions(paidForArr)*/);
        console.log(self.resolveValue());
        console.log(self.amount());
    };

    this.openFormContractEnded = function(data) {
        $(".datepicker").datepicker("disable"); // FIXME replace by knockoutJS datepicker handler
        $('#contract-resolve-ended').dialog('open');
        $(".datepicker").datepicker("enable");
    };

    this.closeForm = function() {
        $('#contract-resolve-late').dialog('close');
    };

    this.closeFormEnded = function() {
        $('#contract-resolve-ended').dialog('close');
    };

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
}
