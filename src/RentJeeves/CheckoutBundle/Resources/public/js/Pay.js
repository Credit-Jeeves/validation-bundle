function Pay(parent, contractId) {
    ko.cleanNode(jQuery('#pay-popup').get(0));

    var self = this;
    var contract = parent.getContractById(contractId);
    this.contract = contract;
    var current = 0;
    this.isValidUser = ko.observable(true);
    this.isPidVerificationSkipped = ko.observable(contract.isPidVerificationSkipped);
    this.infoMessage = ko.observable(null);

    this.getCurrentStep = function() {
        return steps[current];
    };

    this.previous = function() {
        window.formProcess.removeAllErrors('#pay-popup');
        current--;
        this.step(steps[current]);
    };

    this.isProcessQuestion = false;

    var forms = {
        'details': 'rentjeeves_checkoutbundle_paymenttype',
        'source': 'rentjeeves_checkoutbundle_paymentaccounttype',
        'user': 'rentjeeves_checkoutbundle_userdetailstype',
        'questions': 'questions',
        'balance_only': 'rentjeeves_checkoutbundle_paymentbalanceonlytype',
        'pay': 'rentjeeves_checkoutbundle_paymenttype'
    };

    var steps = ['details', 'source', 'user', 'questions', 'pay', 'finish'];

    this.passedSteps = ko.observableArray([]);

    if ('passed' == parent.verification) {
        this.passedSteps.push(steps.splice(2, 1)[0]);
        this.passedSteps.push(steps.splice(2, 1)[0]);
    }
    this.step = ko.observable();

    this.isPassed = function(step) {
        return this.passedSteps().indexOf(step) >= 0;
    };


    this.step.subscribe(function(newValue) {

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

        switch (newValue) {
            case 'details':
                break;
            case 'source':
                break;
            case 'user':
                if (self.isPidVerificationSkipped()) {
                    onSuccessStep([]);
                    break;
                }
                self.isValidUser(true);
                break;
            case 'questions':
                if (self.isPidVerificationSkipped()) {
                    onSuccessStep([]);
                    break;
                }

                if (parent.questions) {
                    break;
                }

                jQuery('#pay-popup').showOverlay();
                jQuery.ajax({
                    url: Routing.generate('experian_pidkiq_get'),
                    type: 'POST',
                    timeout: 30000, // 30 secs
                    error: function(jqXHR, textStatus, errorThrown) {
                        jQuery('#pay-popup').hideOverlay();
                        window.formProcess.reLogin(jqXHR, errorThrown);
                        window.formProcess.addFormError('#vi-questions', errorThrown);
                    },
                    success: function(data, textStatus, jqXHR) {
                        jQuery('#pay-popup').hideOverlay();
                        if (data['isValidUser'] !== undefined && data['isValidUser'] === false) {
                            self.isValidUser(false);
                        } else {
                            self.isValidUser(true);
                        }
                        if (data['status'] && 'error' == data['status']) {
                            window.formProcess.addFormError('#vi-questions', data['error']);
                            self.isProcessQuestion = true;
                            return;
                        }
                        parent.questions = data; //TODO add identity check
                        self.questions(data);
                    }
                });
                break;
            case 'pay':
                break;
        }
    });

    this.step('details');

    var finishDate = new Date(contract.finishAt);

    this.propertyFullAddress = new Address(this, window.addressesViewModels);
    this.propertyFullAddress.number(contract.property.number);
    this.propertyFullAddress.street(contract.property.street);
    this.propertyFullAddress.city(contract.property.city);
    this.propertyFullAddress.zip(contract.property.zip);
    this.propertyFullAddress.district(contract.property.district);
    this.propertyFullAddress.area(contract.property.area);
    if (typeof contract.unit == 'undefined') { // TODO check and may be remove
        this.propertyFullAddress.unit('');
    } else {
        this.propertyFullAddress.unit(contract.unit.name);
    }

    this.propertyAddress = ko.observable(this.propertyFullAddress.toString());

    this.payment = new Payment(this);
    this.payment.contractId = contract.id;
    this.payment.amount(contract.rent);
    this.payment.endMonth(finishDate.getMonth() + 1);
    this.payment.endYear(finishDate.getYear());
    var paidForArr = parent.getPaidForArrContractById(contractId);
    this.payment.paidForOptions(associativeArrayToOptions(paidForArr, ' '))
    this.payment.dueDates(contract.groupSetting.dueDays);
    for (var i = 0; i < 12; i++) {
        var tempDate = new Date(2000, i, 1);
        this.payment.startMonths.push({number: tempDate.format('n'), name: tempDate.format('M')});
    }

    var today = new Date();
    var year = parseInt(today.format('Y'));
    for (var i = 1; i < 10; i++) {
        this.payment.startYears.push({number: year, name: year});
        year += 1;
    }

    this.isDueDay = function(date) {
        if (-1 == contract.groupSetting.dueDays.indexOf(date.getDate())) {
            return [false, ''];
        }
        return [true, ''];
    };

    this.getPaidFor = ko.computed(function() {
        return paidForArr[self.payment.paidFor()];
    });

    this.total = ko.computed(function() { // It will display to user
        if (self.contract.groupSetting.pay_balance_only) {
            return self.contract.integrated_balance;
        }

        return total = (self.payment.amount()?parseFloat(self.payment.amount()):0) +
            (self.payment.amountOther()?parseFloat(self.payment.amountOther()):0);
    });
    this.totalInput = ko.computed(function() { // It will be put into the hidden input
        if (!self.payment.amount() && !self.payment.amountOther()) {
            return null;
        }
        return self.total();
    });

    this.newUserAddress = ko.observableArray([]);
    this.payment.paymentAccountId.subscribe(function(newValue) {
        if (null != newValue) {
            self.newPaymentAccount(false);
            jQuery.each(self.paymentAccounts(), function(key, val) {
                if (newValue == val.id) {
                    self.paymentSource.type(val.type);
                    self.paymentSource.name(val.name);
                    self.paymentSource.address.addressChoice(val.addressId);
                    var exp = new Date(val.cc_expiration);
                    self.paymentSource.ExpirationMonth(exp.getMonth());
                    self.paymentSource.ExpirationYear(exp.getFullYear());
                }
            });
        }
    });
    this.paymentAccounts = ko.observableArray(window.paymentAccounts);
    this.newPaymentAccount = ko.observable(!this.paymentAccounts().length);

    this.notEmptyPaymentAccount = ko.computed(function() {
        if (self.paymentAccounts().length > 0) {
            return true;
        }
        return false;
    });

    this.isNewPaymentAccount = ko.computed(function() {
        return this.newPaymentAccount() && !this.payment.paymentAccountId();
    }, self);
    this.addNewPaymentAccount = function() {
        self.payment.paymentAccountId(null); // Do not change order!
        self.newPaymentAccount(true);
        self.paymentSource.clear();
    };


    this.fullPayTo = contract.payToName;
    this.settleDays = 3; // All logic logic in "settle" method depends on this value
    this.settle = ko.computed(function() {
        var settleDate = new Date(this.payment.startDate());
        var startDayOfWeek = (0 == settleDate.getDay()?7:settleDate.getDay()); // Move Sunday from 0 to 7
        /* logic: skip weekends */
        var daysAdd = (4 == startDayOfWeek || 6 == startDayOfWeek ? 1 : 0);
        if (0 == daysAdd) {
            daysAdd = (5 == startDayOfWeek ? 2 : 0);
        }
        /* end of logic: skip weekends */

        settleDate.add(/*this.settleDays*/3).days();// see comment of this.settleDays
        var dayOfWeek = (0 == settleDate.getDay()?7:settleDate.getDay()); // Move Sunday from 0 to 7
        var daysShift = 8 - dayOfWeek; // Settle day can't be weekend
        if (2 < daysShift) {
            daysShift = 0;
        }
        settleDate.add(daysShift + daysAdd).days();
        return settleDate.toString('M/d/yyyy');
    }, this);
    this.getLastPaymentDay = ko.computed(function() {
        var finishDate = new Date();
        finishDate.setDate(1);
        finishDate.setMonth(this.payment.endMonth() - 1);
        finishDate.setYear(this.payment.endYear());
        var daysInMonth = Date.getDaysInMonth(parseInt(this.payment.endYear()), parseInt(this.payment.endMonth()) - 1);
        finishDate.setDate(
            this.payment.dueDate() > daysInMonth ?
                daysInMonth :
                this.payment.dueDate()
        );
        return finishDate.toString('M/d/yyyy');
    }, this);

    this.paymentSource = new PaymentSource(this, false, this.propertyFullAddress);
    this.paymentSource.groupId(contract.groupId);
    this.paymentSource.contractId(contract.id);

    this.address = new Address(this, window.addressesViewModels, this.propertyFullAddress);
    this.questions = ko.observable(parent.questions);

    this.getAmount = ko.computed(function() {
        if (self.contract.groupSetting.pay_balance_only) {
            return Format.money(self.contract.integrated_balance);
        } else {
            return Format.money(this.payment.amount());
        }
    }, this);
    this.getOtherAmount = ko.computed(function() {
        return Format.money(this.payment.amountOther());
    }, this);
    this.getTotal = ko.computed(function() {
        return Format.money(this.total());
    }, this);

    var fee = function(isText) {
        var fee = null;
        if ('card' == self.paymentSource.type()) {
            fee = parseFloat(contract.depositAccount.feeCC);
            if (isText) {
                fee += '%'
            }
        } else if ('bank' == self.paymentSource.type()) {
            if (contract.depositAccount.isPassedACH) {
                fee = parseFloat(contract.depositAccount.feeACH);
            } else {
                fee = 0;
            }
            if (isText) {
                fee = Format.money(fee);
            }
        }
        return fee;
    };

    this.getFee = ko.computed(function() {
        return fee(false);
    }, this);

    this.getFeeText = ko.computed(function() {
        return fee(true);
    }, this);

    this.getFeeNote = ko.computed(function() {
        if ('card' == self.paymentSource.type()) {
            return 'checkout.fee.card.note-%FEE%';
        } else if ('bank' == self.paymentSource.type()) {
            return 'checkout.fee.bank.note-%FEE%';
        }
        return null;
    });

    this.getFeeNoteHelp = ko.computed(function() {
        var i18nKey = null;
        if ('card' == self.paymentSource.type()) {
            i18nKey = 'checkout.fee.card.note.help-%FEE%';
        } else if ('bank' == self.paymentSource.type()) {
            i18nKey = 'checkout.fee.bank.note.help-%FEE%';
        }
        return i18nKey ? Translator.trans(i18nKey, {'FEE': fee(true)}) : null;
    });

    var getFeeAmount = function(isText) {
        var fee = 0.00;
        if ('card' == self.paymentSource.type()) {
            fee = parseFloat(contract.depositAccount.feeCC) / 100 * self.total();
        } else if ('bank' == self.paymentSource.type()) {
            fee = parseFloat(contract.depositAccount.feeACH);
        }
        if (isText) {
            fee = Format.money(fee);
        }
        return fee;
    };

    this.getFeeAmountText = ko.computed(function() {
        return getFeeAmount(true);
    }, this);

    this.getTotalWithFee = function() {
        var fee = getFeeAmount();
        return Format.money(parseFloat(this.total()) + fee);
    };

    this.showInfoMessage = function() {
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

    this.isForceSave = ko.computed(function() {
        var result = 'immediate' != this.payment.type();
        this.paymentSource.save(result);
        this.paymentSource.isForceSave(result);
        return result;
    }, this);


    this.stepExist = function(step) {
        return -1 != steps.indexOf(step);
    };

    var addNewAddress = function(newAddress) {
        var address = new Address(null);
        ko.mapping.fromJS(newAddress, {}, address);
        window.addressesViewModels.push(address);
        self.address.clear();
        self.address.addressChoice(newAddress.id);
        self.paymentSource.address.clear();
        self.paymentSource.address.addressChoice(newAddress.id);
        self.newUserAddress.push(address);
    };

    this.currentAddress = ko.computed(function() {
        if (self.paymentSource && self.paymentSource.address.addressChoice()) {
            var result = ko.utils.arrayFirst(window.addressesViewModels, function(address) {
                return address.id() == self.paymentSource.address.addressChoice();
            });
            if (result) {
                return result.toString();
            }
        }

        return '';
    }, this);

    var onSuccessStep = function(data) {
        var currentStep = self.getCurrentStep();
        switch (currentStep) {
            case 'details':
                break;
            case 'source':
                if (data.newAddress) {
                    addNewAddress(data.newAddress);
                }

                if (data.paymentAccount) {
                  // Do not change order of next calls:
                  self.paymentAccounts.push(data.paymentAccount);
                  self.payment.paymentAccountId(data.paymentAccount.id);
                }
                // End
                break;
            case 'user':
                if (data.newAddress) {
                    addNewAddress(data.newAddress);
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

    var sendData = function(url, formId) {


        // HTML5 validation // TODO fix for hidden fields
//        try {
//            jQuery('#' + formId + '_submit').click();
//            if (!jQuery('#' + formId)[0].checkValidity()) {
//                return false;
//            }
//        } catch (e) {
//            alert(e);
//            // form have invalid but hidden fields
//        }

//        if (!formValidator.submit()) {// TODO implement after bug fixes
//            console.log('submit fail');
//            return false;
//        }
        jQuery('#pay-popup').showOverlay();

        var formData = jQuery('#' + formId);
        var data = formData.serializeArray();
        data.push({
            'name': 'contract_id',
            'value': self.contract.id
        });
        jQuery.ajax({
            url: url,
            type: 'POST',
            timeout: 60000, // 30 secs
            dataType: 'json',
            data: jQuery.param(data, false),
//            complete: function(jqXHR, textStatus) {
//                jQuery('#pay-popup').hideOverlay();
//            },
            error: function(jqXHR, textStatus, errorThrown) {
                window.formProcess.removeAllErrors('#pay-popup ');
                jQuery('#pay-popup').hideOverlay();
                window.formProcess.reLogin(jqXHR, errorThrown);
                window.formProcess.addFormError('#' + formId, errorThrown);
            },
            success: function(data, textStatus, jqXHR) {
                window.formProcess.removeAllErrors('#pay-popup ');
                jQuery.each(forms, function(key, formName) {
                    jQuery('#' + formName + ' .error').removeClass('error');
                });

                jQuery('#pay-popup').hideOverlay();
                if (!data.success) {
                    window.formProcess.applyErrors(data);
                    return;
                }
                onSuccessStep(data);
            }
        });
    };

    this.next = function() {
        var currentStep = self.getCurrentStep();
        switch (currentStep) {
            case 'details':
                if (contract.groupSetting.pay_balance_only) {
                    sendData(Routing.generate('checkout_pay_payment'), forms['balance_only']);
                } else {
                    sendData(Routing.generate('checkout_pay_payment'), forms[currentStep]);
                }
                break;
            case 'source':
                if (!self.payment.paymentAccountId() && !self.newPaymentAccount()) {
                    window.formProcess.removeAllErrors('#pay-popup ');
                    window.formProcess.addFormError(
                        '#' + forms[currentStep],
                        Translator.trans('payment_account.error.choice.empty')
                    );
                } else if (self.newPaymentAccount()) {
                    sendData(Routing.generate('checkout_pay_source'), forms[currentStep]);
                } else {
                    self.paymentSource.id(self.payment.paymentAccountId());
                    sendData(Routing.generate('checkout_pay_existing_source'), forms[currentStep]);
                }
                break;
            case 'user':
                if (self.isPidVerificationSkipped()) {
                    onSuccessStep([]);
                    break;
                }
                self.isProcessQuestion = false;
                sendData(Routing.generate('checkout_pay_user'), forms[currentStep]);
                break;
            case 'questions':
                if (self.isPidVerificationSkipped()) {
                    onSuccessStep([]);
                    break;
                }
                if (self.checkFillAllQuestions() === false) {
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
                    onSuccessStep([]);
                }
                //User is invalid so we don't do any think and live error
                break;
            case 'pay':
                if (contract.groupSetting.pay_balance_only) {
                    sendData(Routing.generate('checkout_pay_exec'), forms['balance_only']);
                } else {
                    sendData(Routing.generate('checkout_pay_exec'), forms[currentStep]);
                }
                break;
            case 'finish':
                onSuccessStep([]);
                break;
        }

    };

    this.cancelDialog = function() {
        new Cancel(self.payment.id());
    };

    // Constructor

    if (contract.payment) {
        ko.mapping.fromJS(contract.payment, {}, this.payment);
    }

    jQuery('#pay-popup').dialog({
        width: 650,
        modal: true,
        beforeClose: function( event, ui ) {
            self.paymentAccounts([{id: '', name: ''}]);
            self.newUserAddress([new Address()]);
            jQuery("input.datepicker-field").datepicker("destroy");
        }
    });

//    jQuery("#vi-questions").parent().replaceWith(jQuery("#vi-questions"));
//    jQuery('#vi-questions').slimScroll({
//        alwaysVisible:true,
//        width:330,
//        height:260
//    });

    jQuery('.user-ssn').ssn();

    ko.applyBindings(this, jQuery('#pay-popup').get(0));

    jQuery.each(forms, function(key, formName) {
        jsfv[formName].addError = window.formProcess.addFormError;
        jsfv[formName].removeErrors = function(field) {};
        jQuery('#' + formName).submit(function() {
            self.next();
            return false;
        });
    });

    window.formProcess.removeAllErrors('#pay-popup ');

    /**
     * Checks whether there are unanswered questions.
     * If such questions exist - show message with their numbers and return FALSE
     * else
     * return TRUE
     *
     * @return boolean
     */
    this.checkFillAllQuestions = function () {
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
    }
}
