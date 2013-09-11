function Pay(parent, contractId) {
    ko.cleanNode($('#pay-popup').get(0));

    var contract = parent.getContractById(contractId);
    var current = 0;
    var steps = ['details', 'source', 'verify', 'questions', 'pay'];

    /*  Form fields  */
    this.amount = ko.observable(contract.amount);
    var startDate = new Date(contract.start_at); //TODO implement, it was commented for first step of integration
//    var startDate = new Date();
    this.startDate = ko.observable(startDate.toString('MM/dd/yyyy'));
    this.recurring = ko.observable(false);
    this.type = ko.observable();
    this.ends = ko.observable('cancelled');
    var finishDate = new Date(contract.finish_at);
    this.endsOn = ko.observable(finishDate.toString('MM/dd/yyyy'));
    /* /Form fields/ */

    this.propertyAddress = ko.observable(contract.full_address);
    this.dueDay = ko.observable(contract.due_day);
    this.settleDays = 3; // All logic logic in "settle" method depends on this value
    this.settle = ko.computed(function() {
        var settleDate = new Date(this.startDate());
        var startDayOfWeek = (0 == settleDate.getDay()?7:settleDate.getDay()); // Move Sunday from 0 to 7
        /* logic: skip weekends */
        var daysAdd = (4 == startDayOfWeek || 6 == startDayOfWeek ? 1 : 0);
        if (0 == daysAdd) {
            daysAdd = (5 == startDayOfWeek ? 2 : 0);
        }
        /* end of logic: skip weekends */

        settleDate.add(/*this.settleDays*/3).days();// see comment of this.settleDays
        var dayOfWeek = (0 == settleDate.getDay()?7:settleDate.getDay()); // Move Sunday from 0 to 7
        var daysShift = 8 - dayOfWeek; // Settle day can't be weekend
        if (2 < daysShift) {
            daysShift = 0;
        }
        settleDate.add(daysShift + daysAdd).days();
        return settleDate.toString('MM/dd/yyyy');
    }, this);
    this.getLastPaymentDay = ko.computed(function() {
        return 'on' == this.ends() ? this.endsOn() : finishDate.toString('MM/dd/yyyy');
    }, this);

    this.paymentSource = new PaymentSource(this, false);

    var propertyAddress = contract.property;
    propertyAddress.street = propertyAddress.address;

    this.address = new Address(this, window.addressesViewModels, propertyAddress);

    this.getAmount = ko.computed(function() {
        return '$' + this.amount();
    }, this);
    this.isTodayPaymnet = ko.computed(function() {
        var now = new Date();
        var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        var startDate = new Date(this.startDate());
        return today.getTime() == startDate.getTime();
    }, this);
    this.isForceSave = ko.computed(function() {
        var result = this.recurring() || !this.isTodayPaymnet();
        this.paymentSource.save(result);
        this.paymentSource.isForceSave(result);
        return result;
    }, this);

//    steps.splice(2, 2);
    this.step = ko.observable('details');

    this.stepExist = function(step) {
        return -1 != steps.indexOf(step);
    };

    this.next = function() {
        current++;
        this.step(steps[current]);
    };

    this.submit = function(step) {
        alert('OK');
    };

    this.previous = function() {
        current--;
        this.step(steps[current]);
    };

    // Constructor

    $("#pay-popup").dialog({
        width:650,
        modal:true
    });


    $("input.datepicker-field").datepicker({
        showOn: "button",
        buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
        buttonImageOnly: true,
        showOtherMonths: true,
        selectOtherMonths: true
    });

    $('#vi-questions').slimScroll({
        alwaysVisible:true,
        width:330,
        height:260
    });

    $('.user-ssn').ssn();

    ko.applyBindings(this, $('#pay-popup').get(0));

}
