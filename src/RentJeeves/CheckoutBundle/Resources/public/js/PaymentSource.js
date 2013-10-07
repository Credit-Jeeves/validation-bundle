function PaymentSource(parent, isForceSave, newAddress) {
    var self = this;
    this.paymentAccounts = ko.observableArray(window.paymentAccounts);
    this.id = ko.observable(null);
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

    this.address = new Address(this, window.addressesViewModels, newAddress);
    this.save = ko.observable(isForceSave);
    this.isForceSave = ko.observable(isForceSave);
    this.groupId = ko.observable(null);

    this.getCardNumber = ko.computed(function() {
        var card = self.CardNumber().toString().split('');
        for(var i = 0; i < card.length; i++) {
            if (i > 6 && (card.length - 5) >= i) {
                card[i] = 'X';
            }
        }
        return card.join('');
    });
}
