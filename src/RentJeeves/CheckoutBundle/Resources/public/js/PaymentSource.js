function PaymentSource(parent, isForceSave) {
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
    this.save = ko.observable(isForceSave);
    this.isForceSave = ko.observable(isForceSave);
}
