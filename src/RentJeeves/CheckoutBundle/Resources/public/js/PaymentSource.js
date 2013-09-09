function PaymentSource(parent, isForceSave) {
    var self = this;
    this.addresses = ko.observableArray(window.addressesViewModels);
    this.paymentAccounts = ko.observableArray(window.paymentAccounts);
    this.type = ko.observable('bank');
    this.name = ko.observable('');
    this.PayorName = ko.observable('');
    this.RoutingNumber = ko.observable('');
    this.AccountNumber = ko.observable('');
    this.ACHDepositType = ko.observable(null);
    this.CardNumber = ko.observable('');
    this.VerificationCode = ko.observable('');
    this.ExpirationMonth = ko.observable(null);
    this.ExpirationYear = ko.observable(null);
    this.address = new Address(this);
    this.addressChoice = ko.observable(null);
    this.save = ko.observable(isForceSave);
    this.isForceSave = ko.observable(isForceSave);

    this.isAddNewAddress = ko.observable(!this.addresses().length);
    this.addAddress = function() {
        self.isAddNewAddress(true);
        console.log(self.isAddNewAddress());
    };
}
