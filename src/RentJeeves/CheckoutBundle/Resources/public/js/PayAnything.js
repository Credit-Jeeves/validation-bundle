function PayAnything(parent, contract, defaultParams) {
    var rootNodeName ='#pay-anything-popup';
    var rootNode = jQuery(rootNodeName);

    ko.cleanNode(rootNode.get(0));

    var self = this;

    var forms = {
        'details': 'rentjeeves_checkoutbundle_payanything_paymenttype',
        'source': 'rentjeeves_checkoutbundle_paymentaccounttype_pay_anything',
        'pay': 'rentjeeves_checkoutbundle_payanything_paymenttype'
    };

    var defaultPayFor = null;
    var defaultAmounts = {};
    if (defaultParams) {
        defaultPayFor = defaultParams.payFor;
        defaultAmounts = defaultParams.amounts ? defaultParams.amounts : {};
    }

    var redirectUrl = null;

    // Wizard-popup steps
    var steps = ['details', 'source', 'pay', 'finish'];
    var current = 0;

    self.step = ko.observable();

    self.passedSteps = ko.observableArray([]);

    self.isPassed = function(step) {
        return self.passedSteps().indexOf(step) >= 0;
    };

    self.getCurrentStep = function() {
        return self.step();
    };

    self.stepExist = function(step) {
        return -1 != steps.indexOf(step);
    };

    self.previous = function() {
        window.formProcess.removeAllErrors(rootNodeName);
        current--;
        self.step(steps[current]);
    };

    self.next = function() {
        var currentStep = self.getCurrentStep();
        switch (currentStep) {
            case 'details':
                sendData(Routing.generate('pay_anything_validate_payment'), forms[currentStep]);
                break;
            case 'source':
                if (!self.payment.paymentAccountId() && !self.isNewPaymentAccount()) {
                    window.formProcess.removeAllErrors(rootNodeName);
                    window.formProcess.addFormError(
                        '#' + forms[currentStep],
                        Translator.trans('payment_account.error.choice.empty')
                    );
                } else if (self.isNewPaymentAccount()) {
                    sendData(
                        Routing.generate('checkout_pay_source', {'formNameSuffix':'pay_anything'}),
                        forms[currentStep]
                    );
                } else {
                    self.currentPaymentAccountId(self.payment.paymentAccountId());
                    sendData(
                        Routing.generate('checkout_pay_existing_source', {'formNameSuffix':'pay_anything'}),
                        forms[currentStep]
                    );
                }
                break;
            case 'pay':
                sendData(Routing.generate('pay_anything_exec'), forms[currentStep]);
                break;
            case 'finish':
                successStepHandler([]);
                break;
        }

    };

    self.step.subscribe(function(newValue) {
        // if this step was already passed, then remove it (when user clicks Previous button)
        if (self.passedSteps.indexOf(newValue) >= 0) {
            self.passedSteps.remove(newValue);
        } else {
            var stepNum = steps.indexOf(newValue);
            // if previous step exists, then it is passed
            if (typeof steps[stepNum - 1] != 'undefined') {
                self.passedSteps.push(steps[stepNum - 1]);
            }
        }
    });

    var successStepHandler = function(data) {
        var currentStep = self.getCurrentStep();
        switch (currentStep) {
            case 'details':
                break;
            case 'source':
                if (data.newAddress) {
                    self.addNewAddress(data.newAddress);
                }

                if (data.paymentAccount) {
                    // Do not change order of next calls:
                    self.pushPaymentAccount(data.paymentAccount);
                    self.currentPaymentAccountId(data.paymentAccount.id);
                }
                // End
                break;
            case 'pay':
                if (data.redirectUrl) {
                    redirectUrl = data.redirectUrl;
                }
                break;
            case 'finish':
                rootNode.dialog('close');
                jQuery('body').showOverlay();
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                } else {
                    window.location.reload();
                }
                return;
                break;
        }

        self.step(steps[++current]);
    };

    var sendData = function(url, formId) {
        rootNode.showOverlay();

        var formData = jQuery('#' + formId);
        var data = formData.serializeArray();
        data.push({
            'name': 'contract_id',
            'value': self.contractId()
        });
        if (self.getCurrentStep() == 'source') {
            data.push({
                'name': 'deposit_account_type',
                'value': self.payFor()
            });
        }

        jQuery.ajax({
            url: url,
            type: 'POST',
            timeout: 60000, // 30 secs
            dataType: 'json',
            data: jQuery.param(data, false),
            error: function(jqXHR, textStatus, errorThrown) {
                window.formProcess.removeAllErrors(rootNodeName);
                rootNode.hideOverlay();
                window.formProcess.reLogin(jqXHR, errorThrown);
                window.formProcess.addFormError('#' + formId, errorThrown);
            },
            success: function(data) {
                window.formProcess.removeAllErrors(rootNodeName);
                jQuery.each(forms, function(key, formName) {
                    jQuery('#' + formName + ' .error').removeClass('error');
                });

                rootNode.hideOverlay();
                if (!data.success) {
                    window.formProcess.applyErrors(data);
                    return;
                }
                successStepHandler(data);
            }
        });
    };

    self.prepareDialog = function () {
        rootNode.dialog({
            width: 610,
            modal: true,
            beforeClose: function( event, ui ) {
                current = 0;
                self.step(steps[current]);
            }
        });

        jQuery.each(forms, function(key, formName) {
            jsfv[formName].addError = window.formProcess.addFormError;
            jsfv[formName].removeErrors = function(field) {};
            jQuery('#' + formName).submit(function() {
                self.next();
                return false;
            });
        });

        window.formProcess.removeAllErrors(rootNodeName);
    };

    var loadedGroupId = null;

    self.loadPayForData = function(groupId) {
        if (groupId != loadedGroupId) {
            rootNode.showOverlay();
            jQuery.getJSON(
                Routing.generate('pay_anything_pay_for_list', {'groupId': groupId}),
                function(data) {
                    if (data) {
                        loadedGroupId = groupId;
                        self.availablePayFor(data);
                        if (defaultPayFor) {
                            var payFor = ko.utils.arrayFirst(self.availablePayFor(), function(item) {
                                return item.value == defaultPayFor;
                            });
                            if (payFor) {
                                self.payFor(payFor.value);
                            } else {
                                payFor = ko.utils.arrayFirst(self.availablePayFor(), function(item) {
                                    return (item.value in defaultAmounts);
                                });
                                if (payFor) {
                                    self.payFor(payFor.value);
                                }
                            }
                        }
                        rootNode.hideOverlay();
                    }
                }
            );
        }
    };

    self.loadPayForData(contract.groupId);

    self.contract = ko.observable(contract);

    self.contract.subscribe(function (newContract) {
        if (newContract) {
            self.prepareDialog();
            self.loadPayForData(self.contract().groupId);
            self.mapPayment(newContract);
        }
    });

    self.contractId = ko.computed(function () {
        var contract = ko.unwrap(self.contract);
        return  contract ? contract.id : null;
    });

    self.disableCreditCard = ko.computed(function () {
        var contract = ko.unwrap(self.contract);
        return  contract ? contract.disableCreditCard : false;
    });

    self.propertyAddress = ko.computed(function() {
        var propertyFullAddress = new Address(self, self.addresses);

        ko.mapping.fromJS(self.contract().property, {}, propertyFullAddress);
        if (self.contract().unit) {
            propertyFullAddress.unit(self.contract().unit.name);
        }

        return propertyFullAddress;
    });

    ko.utils.extend(self, new PayAddress(self, self.propertyAddress));

    // Connected Payment Source Component
    // Component should be connected after contractId and disableCreditCard and before it should be using
    ko.utils.extend(
        self,
        new PaymentSourceViewModel(
            self,
            self.contractId,
            {
                'disableCreditCard': self.disableCreditCard
            }
        )
    );

    self.payment = new Payment(self);

    self.payFor = ko.observable(null);

    self.payFor.subscribe(function (newPayFor) {
        if (newPayFor in defaultAmounts) {
            self.payment.amount(defaultAmounts[newPayFor]);
        } else {
            self.payment.amount(null);
        }
    });

    self.availablePayFor = ko.observableArray([]);

    self.payForText = ko.computed(function() {
        if (self.payFor()) {
            var payFor = ko.utils.arrayFirst(self.availablePayFor(), function(item) {
                return item.value == self.payFor();
            });
            if (payFor) {
                return payFor.name;
            }
        }

        return '';
    });

    self.changePaymentAccountHandler = function(newPaymentAccountId) {
        self.payment.paymentAccountId(newPaymentAccountId);
        self.billingaddress.addressChoice(self.currentPaymentAccount().addressId());
    };

    self.afterMapPaymentAccountsHandler = function () {
        if (self.payment.paymentAccountId()) {
            self.currentPaymentAccountId(self.payment.paymentAccountId());
        } else {
            self.currentPaymentAccountId(null);
        }
    };

    self.mapPayment = function (contract) {
        self.payment.clear();

        var finishDate = new Date(contract.finishAt);

        var paymentData = {
            'type' : 'one_time',
            'contractId' : contract.id,
            'endMonth' : finishDate.getMonth() + 1,
            'endYear' : finishDate.getYear()
        };

        ko.mapping.fromJS(paymentData, {} , self.payment);
    };

    self.infoMessage = ko.observable(null);

    self.showInfoMessage = function() {
        if ('finish' == self.step() && 'one_time' == self.payment.type() && self.payment.startDate()) {
            var now = new Date();
            var startOn = Date.parseExact(self.payment.startDate(),  "M/d/yyyy");
            if (startOn &&
                now.getDate() == startOn.getDate() &&
                now.getMonth() == startOn.getMonth() &&
                now.getFullYear()== startOn.getFullYear()
            ) {
                return true;
            }
        }

        return false;
    };

    ko.utils.extend(self, new PayDatesComputing(self));

    self.fullPayTo = ko.computed(function() {
        return self.contract() ? self.contract().payToName : '';
    });

    ko.utils.extend(self, new PayMoneyComputing(self, self.contract));

    // Constructor

    self.step('details');

    self.prepareDialog();

    self.mapPayment(contract);

    ko.applyBindings(self, rootNode.get(0));
}
