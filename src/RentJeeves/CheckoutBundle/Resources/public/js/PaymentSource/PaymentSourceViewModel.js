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

    self.settings = new PaymentAccountSettings(settings);

    self.defaultType = ko.pureComputed(function () {
        if (typeof (defaultType) == 'undefined' || !self.settings.allowPaymentSourceTypes[defaultType]()) {
            defaultType = self.settings.allowPaymentSourceTypes['bank']() ? 'bank' : 'card';
        }

        return defaultType;
    });

    self.contractId = ko.observable(null);
    if (contractId) {
        self.contractId = contractId;

        self.contractId.subscribe(function (newValue) {
            if (newValue) {
                self.load(newValue);
            }
        });
    }

    self.paymentAccounts = ko.observableArray([]);

    self.currentPaymentAccountId = ko.observable(null);

    self.isNewPaymentAccount = ko.observable(false);

    self.currentPaymentAccount = ko.observable(new PaymentAccount({'contractId' : contractId}, self.defaultType()));

    self.currentPaymentAccountId.subscribe(function(newPaymentAccountId) {
        changePaymentAccountHandler(newPaymentAccountId);
    });

    self.feeDisplay = function (type) {
        var fee = null;
        if (self.settings.feeSettings[type].type() == 'percentage') {
            fee = parseFloat(self.settings.feeSettings[type].value());
            fee = fee ? fee + '%' : 0;
        } else {
            fee = Format.money(self.settings.feeSettings[type].value());
        }

        return fee ? fee : '$0.00';
    };

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
            self.currentPaymentAccount(new PaymentAccount({'contractId' : contractId}, self.defaultType()));
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
        self.currentPaymentAccount(new PaymentAccount({'contractId' : contractId}, self.defaultType()));
        self.isNewPaymentAccount(true);
    };

    /**
     * @param sourceType
     * @return boolean
     */
    self.isAvailablePaymentSourceType = function(sourceType) {
        return self.settings.allowPaymentSourceTypes[sourceType];
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
        // add binding for hide no available payment source types
        ko.utils.arrayForEach(document.getElementsByClassName('payment_source_type'), function (element) {
            element.parentNode.setAttribute(
                "data-bind",
                "visible: isAvailablePaymentSourceType('" + element.value + "')"
            );
        });
        ko.utils.arrayForEach(
            document.querySelectorAll('#payment-type-with-fee .payment-fee-value'),
            function (element) {
                if (element && element.dataset && element.dataset.paymentType) {
                    element.setAttribute(
                        "data-bind",
                        "text: feeDisplay('" + element.dataset.paymentType + "')"
                    );
                }
            }
        );
    };

    // Constructor

    self.init();
}
