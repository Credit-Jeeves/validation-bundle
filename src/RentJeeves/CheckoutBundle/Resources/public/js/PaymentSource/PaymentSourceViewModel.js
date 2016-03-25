/**
 *
 * @param parent
 * @param contractId ko.observable
 * @param settings ko.observable
 * @param defaultType
 * @constructor
 */
function PaymentSourceViewModel(parent, contractId, settings, defaultType) {
    var self = this;

    if (typeof (defaultType) == 'undefined') {
        defaultType = 'bank';
    }

    self.contractId = ko.observable(null);

    self.disableCreditCard = ko.observable(false);

    self.allowDebitCard = ko.observable(false);

    if (contractId) {
        self.contractId = contractId;

        self.contractId.subscribe(function (newValue) {
            if (newValue) {
                self.load(newValue);
            }
        });
    }

    if (settings && settings.disableCreditCard) {
        self.disableCreditCard = settings.disableCreditCard;
    }
    if (settings && settings.allowDebitCard) {
        self.allowDebitCard = settings.allowDebitCard;
    }

    self.paymentAccounts = ko.observableArray([]);

    self.currentPaymentAccountId = ko.observable(null);

    self.isNewPaymentAccount = ko.observable(false);

    self.currentPaymentAccount = ko.observable(new PaymentAccount({'contractId' : contractId}, defaultType));

    self.currentPaymentAccountId.subscribe(function(newPaymentAccountId) {
        changePaymentAccountHandler(newPaymentAccountId);
    });

    /**
     * Handler should update currentPaymentAccount
     * @param newPaymentAccountId
     */
    var changePaymentAccountHandler = function (newPaymentAccountId) {
        if (newPaymentAccountId) {
            var paymentAccount = ko.utils.arrayFirst(self.paymentAccounts(), function (paymentAccount) {
                return paymentAccount().id() == newPaymentAccountId;
            });
            if (paymentAccount) {
                self.currentPaymentAccount(paymentAccount());
                self.isNewPaymentAccount(false);
            }
        } else {
            self.currentPaymentAccount(new PaymentAccount({'contractId' : contractId}, defaultType));
            self.isNewPaymentAccount(true);
        }
        if (typeof (parent.changePaymentAccountHandler) === 'function') {
            parent.changePaymentAccountHandler(newPaymentAccountId);
        }
    };

    var beforeMapPaymentAccountsHandler = function () {
        if (typeof (parent.beforeMapPaymentAccountsHandler) === 'function') {
            parent.beforeMapPaymentAccountsHandler(parent);
        }
    };

    var afterMapPaymentAccountsHandler = function (owner) {
        self.isNewPaymentAccount(self.paymentAccounts().length < 1);
        if (typeof (parent.afterMapPaymentAccountsHandler) === 'function') {
            parent.afterMapPaymentAccountsHandler(parent);
        }
    };

    /**
     * Mapping payment_accounts data that was retrieved from server
     *
     * @param paymentAccounts
     */
    self.mapPaymentAccounts = function(paymentAccounts) {
        beforeMapPaymentAccountsHandler();
        self.paymentAccounts.removeAll();
        var mappedArray = jQuery.map(paymentAccounts, function(paymentAccount) {
            if (contractId) {
                paymentAccount.contractId = contractId;
            }
            return ko.observable(new PaymentAccount(paymentAccount));
        });
        self.paymentAccounts(mappedArray);
        afterMapPaymentAccountsHandler();
    };

    self.pushPaymentAccount = function (paymentAccountData) {
        self.paymentAccounts.push(ko.observable(new PaymentAccount(paymentAccountData)));
    };

    /**
     * Add new payment account
     */
    self.addNewPaymentAccount = function() {
        self.currentPaymentAccountId(null);
        self.currentPaymentAccount(new PaymentAccount({'contractId' : contractId}, defaultType));
        self.isNewPaymentAccount(true);
    };

    /**
     * Load initialization data from server
     */
    self.load = function(contractId) {
        // Retrieve payment accounts from server by contract if contract is null should get all payment accounts
        jQuery.getJSON(
            Routing.generate('payment_accounts_list', {'contractId': contractId}),
            function(data) {
                self.mapPaymentAccounts(data);
            }
        );
    };

    /**
     * Init object
     */
    self.init = function() {
        if (parent.paymentAccounts && typeof(parent.paymentAccounts()) === 'object') {
            self.mapPaymentAccounts(parent.paymentAccounts());
        } else {
            self.load(self.contractId());
        }
        jQuery('input:radio[value="card"]')
            .closest('label.radio')
            .attr('data-bind', 'visible: !disableCreditCard()');
        jQuery('input:radio[value="debit_card"]')
            .closest('label.radio')
            .attr('data-bind', 'visible: (!disableCreditCard() && allowDebitCard())');
    };


    // Constructor

    self.init();
}
