function Pay(data, contractId) {
    ko.cleanNode($('#pay-popup').get(0));

    this.step = ko.observable(1);

    this.next = function() {
       this.step(this.step() + 1);
    };

    this.previous = function() {
       this.step(this.step() - 1);
    };

    // Constructor

    $("#pay-popup").dialog({
        width:650,
        modal:true
    });


    $("#pay-popup #payment-start,#pay-popup #payment-end,#pay-popup #due").datepicker({
        showOn: "button",
        buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
        buttonImageOnly: true,
        showOtherMonths: true,
        selectOtherMonths: true
    });
    ko.applyBindings(this, $('#pay-popup').get(0));

}
