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

    this.address = ko.observable(contract.full_address);
    this.dueDay = ko.observable(contract.due_day);
    this.settleDays = 3;
    this.settle = ko.computed(function() {
        var settleDate = new Date(this.startDate());
        settleDate.add(this.settleDays).days();
        return settleDate.toString('MM/dd/yyyy');
    }, this);
    this.getLastPaymentDay = ko.computed(function() {
        return 'on' == this.ends() ? this.endsOn() : finishDate.toString('MM/dd/yyyy');
    }, this);

    this.paymentSource = new PaymentSource(this, false);
    this.paymentSource.address.street(contract.property.address);
    this.paymentSource.address.zip(contract.property.zip);
    this.paymentSource.address.area(contract.property.area);
    this.paymentSource.address.city(contract.property.city);

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

    $('.user-ssn').ssn();

    ko.applyBindings(this, $('#pay-popup').get(0));

}
