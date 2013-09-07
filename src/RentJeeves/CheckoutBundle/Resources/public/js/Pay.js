function Pay(parent, contractId) {
    ko.cleanNode($('#pay-popup').get(0));

    var contract = parent.getContractById(contractId);
    var current = 0;
    var steps = ['details', 'source', 'verify', 'pay'];

    /*  Form fields  */
    this.amount = ko.observable(contract.amount);
    var startDate = new Date(contract.start_at);
    this.startDate = ko.observable(startDate.getMonth() + '/' + startDate.getDay() + '/' + startDate.getFullYear());
    this.recurring = ko.observable(false);
    this.type = ko.observable();
    this.ends = ko.observable('cancelled');
    var finishDate = new Date(contract.finish_at);
    this.endsOn = ko.observable(finishDate.getMonth() + '/' + finishDate.getDay() + '/' + finishDate.getFullYear());
    /* /Form fields/ */

    this.address = ko.observable(contract.full_address);

    this.paymentSource = new PaymentSource(this, false);

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

//    steps.splice(2, 1);
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
    ko.applyBindings(this, $('#pay-popup').get(0));

}
