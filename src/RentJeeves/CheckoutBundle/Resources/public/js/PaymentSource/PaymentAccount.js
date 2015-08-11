function PaymentAccount(data, defaultType) {
    var self = this;
    if (typeof(defaultType) == 'undefined') {
        defaultType = 'bank';
    }

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

    var mapping = {
        'cc_expiration': {
            create: function(options) {
                var expDate = new Date(options.data);
                options.parent.ExpirationMonth(expDate.getMonth());
                options.parent.ExpirationYear(expDate.getFullYear());
                delete options.parent.cc_expiration;
            }
        },
        'ignore' : ['addressId', 'deposit_accounts']
    };

    ko.mapping.fromJS(data, mapping, self);

    this.clear = function () {
        self.id(null);
        self.type(defaultType);
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
    }
}
