function PaymentSource(parent, isForceSave, newAddress, defaultType) {
    if (typeof(defaultType) == 'undefined') {
        defaultType = 'bank';
    }
    var self = this;
    this.id = ko.observable(null);
    this.type = ko.observable(defaultType);
    this.name = ko.observable('');
    this.PayorName = ko.observable('');
    this.RoutingNumber = ko.observable('');
    this.AccountNumber = ko.observable('');
    this.ACHDepositType = ko.observable(null);
    this.CardAccountName = ko.observable('');
    this.CardNumber = ko.observable('');
    this.VerificationCode = ko.observable('');
    this.ExpirationMonth = ko.observable(null);
    this.ExpirationYear = ko.observable(null);

    this.address = new Address(this, window.addressesViewModels, newAddress);
    this.save = ko.observable(isForceSave);
    this.isForceSave = ko.observable(isForceSave);
    this.groupId = ko.observable(null);
    this.contractId = ko.observable(null);

    this.getCardNumber = ko.computed(function() {
        var card = self.CardNumber().toString().split('');
        for(var i = 0; i < card.length; i++) {
            if (i > 6 && (card.length - 5) >= i) {
                card[i] = 'X';
            }
        }
        return card.join('');
    });



    this.clear = function() {
        self.type(defaultType);
        self.id(null);
        self.name('');
        self.PayorName('');
        self.RoutingNumber('');
        self.AccountNumber('');
        self.ACHDepositType(null);
        self.CardAccountName('');
        self.CardNumber('');
        self.VerificationCode('');
        self.ExpirationMonth(null);
        self.ExpirationYear(null);
//        self.save(isForceSave);
//        self.isForceSave(isForceSave);

        self.address.clear();
    };
}
