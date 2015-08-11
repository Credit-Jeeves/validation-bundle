function Pay(parent, contract) {
    ko.cleanNode(jQuery('#pay-popup').get(0));

    var self = this;

    var forms = {
        'details': 'rentjeeves_checkoutbundle_paymenttype',
        'source': 'rentjeeves_checkoutbundle_paymentaccounttype',
        'user': 'rentjeeves_checkoutbundle_userdetailstype',
        'questions': 'questions',
        'balance_only': 'rentjeeves_checkoutbundle_paymentbalanceonlytype',
        'pay': 'rentjeeves_checkoutbundle_paymenttype'
    };

    // Wizard-popup steps
    var steps = ['details', 'source', 'user', 'questions', 'pay', 'finish'];
    var current = 0;

    self.step = ko.observable();

    self.passedSteps = ko.observableArray([]);

    if ('passed' == parent.verification) {
        self.passedSteps.push(steps.splice(2, 1)[0]);
        self.passedSteps.push(steps.splice(2, 1)[0]);
    }

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
        window.formProcess.removeAllErrors('#pay-popup');
        current--;
        self.step(steps[current]);
    };

    self.next = function() {
        var currentStep = self.getCurrentStep();
        switch (currentStep) {
            case 'details':
                if (self.contract().groupSetting.pay_balance_only) {
                    sendData(Routing.generate('checkout_pay_payment'), forms['balance_only']);
                } else {
                    sendData(Routing.generate('checkout_pay_payment'), forms[currentStep]);
                }
                break;
            case 'source':
                if (!self.payment.paymentAccountId() && !self.isNewPaymentAccount()) {
                    window.formProcess.removeAllErrors('#pay-popup ');
                    window.formProcess.addFormError(
                        '#' + forms[currentStep],
                        Translator.trans('payment_account.error.choice.empty')
                    );
                } else if (self.isNewPaymentAccount()) {
                    sendData(Routing.generate('checkout_pay_source'), forms[currentStep]);
                } else {
                    self.currentPaymentAccountId(self.payment.paymentAccountId());
                    sendData(Routing.generate('checkout_pay_existing_source'), forms[currentStep]);
                }
                break;
            case 'user':
                if (self.isPidVerificationSkipped()) {
                    successStepHandler([]);
                    break;
                }
                self.isProcessQuestion = false;
                sendData(Routing.generate('checkout_pay_user'), forms[currentStep]);
                break;
            case 'questions':
                if (self.isPidVerificationSkipped()) {
                    successStepHandler([]);
                    break;
                }
                if (checkFillAllQuestions() === false) {
                    break;
                }
                //User is valid and we have question so we can try process it
                if (self.isValidUser() && !self.isProcessQuestion) {
                    sendData(Routing.generate('experian_pidkiq_execute'), forms[currentStep]);
                    self.isProcessQuestion = true;
                    //Wrong answer for question, but we have user and we can move to next step
                } else if(self.isValidUser() && self.isProcessQuestion) {
                    window.formProcess.removeAllErrors('#pay-popup ');
                    jQuery.each(forms, function(key, formName) {
                        jQuery('#' + formName + ' .error').removeClass('error');
                    });
                    successStepHandler([]);
                }
                //User is invalid so we don't do any think and live error
                break;
            case 'pay':
                if (self.contract().groupSetting.pay_balance_only) {
                    sendData(Routing.generate('checkout_pay_exec'), forms['balance_only']);
                } else {
                    sendData(Routing.generate('checkout_pay_exec'), forms[currentStep]);
                }
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

        changeStepHandler(newValue);
    });

    var changeStepHandler = function (newStep) {
        switch (newStep) {
            case 'details':
                break;
            case 'source':
                break;
            case 'user':
                if (self.isPidVerificationSkipped()) {
                    successStepHandler([]);
                    break;
                }
                self.isValidUser(true);
                break;
            case 'questions':
                if (self.isPidVerificationSkipped()) {
                    successStepHandler([]);
                    break;
                }

                self.loadQuestions();

                break;
            case 'pay':
                break;
        }

    };

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
            case 'user':
                if (data.newAddress) {
                    self.addNewAddress(data.newAddress);
                }
                break;
            case 'questions':
                if (data.verification !== undefined) {
                    parent.verification = data.verification;
                }
                steps.splice(2, 2);
                current -= 2;
                break;
            case 'pay':
                break;
            case 'finish':
                jQuery('#pay-popup').dialog('close');
                jQuery('body').showOverlay();
                window.location.reload();
                return;
                break;
        }

        self.step(steps[++current]);
    };

    /**
     * Checks whether there are unanswered questions.
     * If such questions exist - show message with their numbers and return FALSE
     * else
     * return TRUE
     *
     * @return boolean
     */
    var checkFillAllQuestions = function () {
        window.formProcess.removeAllErrors('#pay-popup ');

        var questionsDiv = $('div#questions>div').has('input:radio');
        var countQuestionsWithoutAnswer = 0;

        questionsDiv.each(function () {
            if ($(this).find('input:radio:checked').length === 0) {
                countQuestionsWithoutAnswer++;
            }
        });

        if (countQuestionsWithoutAnswer > 0) {
            var message = Translator.trans('pidkiq.error.unanswered_questions', { COUNT: countQuestionsWithoutAnswer });
            window.formProcess.addFormError(
                '#' + forms[self.getCurrentStep()],
                message
            );

            return false;
        }

        return true;
    };

    var sendData = function(url, formId) {
        jQuery('#pay-popup').showOverlay();

        var formData = jQuery('#' + formId);
        var data = formData.serializeArray();
        data.push({
            'name': 'contract_id',
            'value': self.contractId()
        });
        jQuery.ajax({
            url: url,
            type: 'POST',
            timeout: 60000, // 30 secs
            dataType: 'json',
            data: jQuery.param(data, false),
            error: function(jqXHR, textStatus, errorThrown) {
                window.formProcess.removeAllErrors('#pay-popup ');
                jQuery('#pay-popup').hideOverlay();
                window.formProcess.reLogin(jqXHR, errorThrown);
                window.formProcess.addFormError('#' + formId, errorThrown);
            },
            success: function(data) {
                window.formProcess.removeAllErrors('#pay-popup ');
                jQuery.each(forms, function(key, formName) {
                    jQuery('#' + formName + ' .error').removeClass('error');
                });

                jQuery('#pay-popup').hideOverlay();
                if (!data.success) {
                    window.formProcess.applyErrors(data);
                    return;
                }
                successStepHandler(data);
            }
        });
    };

    self.prepareDialog = function () {
        jQuery('#pay-popup').dialog({
            width: 650,
            modal: true,
            beforeClose: function( event, ui ) {
                current = 0;
                self.step(steps[current]);
                jQuery("input.datepicker-field").datepicker("destroy");
            }
        });

        jQuery('.user-ssn').ssn();

        jQuery.each(forms, function(key, formName) {
            jsfv[formName].addError = window.formProcess.addFormError;
            jsfv[formName].removeErrors = function(field) {};
            jQuery('#' + formName).submit(function() {
                self.next();
                return false;
            });
        });

        window.formProcess.removeAllErrors('#pay-popup ');
    };

    self.contract = ko.observable(contract);

    self.contract.subscribe(function (newContract) {
        if (newContract) {
            self.prepareDialog();
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
        propertyFullAddress.unit(self.contract().unit.name);

        return propertyFullAddress;
    });

    ko.utils.extend(self, new PayAddress(self, self.propertyAddress));

    // Connected Payment Source Component
    // Component should be connected after contractId and disableCreditCard and before it should be using
    ko.utils.extend(self, new PaymentSourceViewModel(self, self.contractId, self.disableCreditCard));

    self.payment = new Payment(self);

    self.paidForArr = ko.observable(null);

    self.changePaymentAccountHandler = function(newPaymentAccountId) {
        self.payment.paymentAccountId(newPaymentAccountId);
    };

    self.afterMapPaymentAccountsHandler = function () {
        if (self.payment.paymentAccountId()) {
            self.currentPaymentAccountId(self.payment.paymentAccountId());
        } else {
            self.currentPaymentAccountId(null);
        }
    };

    self.mapPayment = function (contract) {
        self.paidForArr(parent.getPaidForArrContractById(contract.id));
        self.payment.clear();

        var finishDate = new Date(contract.finishAt);

        var paymentData = {
            'contractId' : contract.id,
            'amount' : contract.rent,
            'endMonth' : finishDate.getMonth() + 1,
            'endYear' : finishDate.getYear(),
            'paidForOptions' : associativeArrayToOptions(ko.unwrap(self.paidForArr), ''),
            'dueDates' : contract.groupSetting.dueDays
        };

        ko.mapping.fromJS(paymentData, {} , self.payment);

        if (contract.payment) {
            ko.mapping.fromJS(contract.payment, {}, self.payment);
        }
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


    self.isPidVerificationSkipped = ko.computed(function() {
        var contract = ko.unwrap(self.contract);
        return contract ? contract.isPidVerificationSkipped : false
    });

    ko.utils.extend(self, new UserVerification(self, self.isPidVerificationSkipped));

    ko.utils.extend(self, new PayDatesComputing(self));

    ko.utils.extend(self, new PayMoneyComputing(self, self.contract));


    self.cancelDialog = function() {
        new Cancel(self.payment.id());
    };

    // Constructor

    self.step('details');

    self.prepareDialog();

    self.mapPayment(contract);

    ko.applyBindings(self, jQuery('#pay-popup').get(0));
}
