function Pay(parent, contractId) {
    ko.cleanNode($('#pay-popup').get(0));

    /*  Form fields  */
    this.amount = ko.observable(0);
    this.startDate = ko.observable(null);
    this.recurring = ko.observable(false);
    this.type = ko.observable();
    this.ends = ko.observable('cancelled');
    this.endsOn = ko.observable(null);
    /* /Form fields/ */

    var current = 0;
    var steps = ['details', 'source', 'verify', 'pay'];

    this.paymentSource = new PaymentSource(this);

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
