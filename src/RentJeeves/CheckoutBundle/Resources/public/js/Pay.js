function Pay(parent, contractId) {
    ko.cleanNode($('#pay-popup').get(0));

    var self = this;
    var contract = parent.getContractById(contractId);
    var current = 0;
    this.isValidUser = ko.observable(true);
    this.isProcessQuestion = false;

    var forms = {
        'details': 'rentjeeves_checkoutbundle_paymenttype',
        'source': 'rentjeeves_checkoutbundle_paymentaccounttype',
        'user': 'rentjeeves_checkoutbundle_userdetailstype',
        'questions': 'questions'
    };

    var steps = ['details', 'source', 'user', 'questions', 'pay'];

    this.passedSteps = ko.observableArray([]);

    if ('passed' == parent.verification) {
        this.passedSteps.push(steps.splice(2, 1)[0]);
        this.passedSteps.push(steps.splice(2, 1)[0]);
    }
    this.step = ko.observable();

    this.isPassed = function(step) {
        return this.passedSteps().indexOf(step) >= 0;
    }

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
                self.isValidUser(true);
                break;
            case 'questions':
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
    if (typeof contract.unit == 'undefined') {
        this.propertyFullAddress.unit('');
    } else {
        this.propertyFullAddress.unit(contract.unit.name);
    }

    this.propertyAddress = ko.observable(this.propertyFullAddress.toString());

    if (contract.paidTo !== undefined) {
        var paymentDate = new Date(contract.paidTo);
    } else if(contract.startAt !== undefined) {
        var paymentDate = new Date(contract.startAt);
    } else {
        var paymentDate = new Date();
    }

    this.payment = new Payment(this, paymentDate);
    this.payment.contractId = contract.id;
    this.payment.amount(contract.rent);
    this.payment.endMonth(finishDate.getMonth() + 1);
    this.payment.endYear(finishDate.getYear());

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
    this.paymentAccounts = ko.observableArray([]);
    jQuery.each(window.paymentAccounts, function (key, val) {
        if (contract.groupId == val.groupId) {
            self.paymentAccounts.push(val);
        }
    });


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
        var finishDate = new Date(contract.finishAt);
        if ('on' == this.payment.ends()) {
            finishDate.setMonth(this.payment.endMonth() - 1);
            finishDate.setYear(this.payment.endYear());
            finishDate.setDate(
                this.payment.dueDate() > finishDate.getDaysInMonth() ?
                    finishDate.getDaysInMonth() :
                    this.payment.dueDate()
            );
        }
        return finishDate.toString('M/d/yyyy');
    }, this);

    this.paymentSource = new PaymentSource(this, false, this.propertyFullAddress);
    this.paymentSource.groupId(contract.groupId);

    this.address = new Address(this, window.addressesViewModels, this.propertyFullAddress);
    this.questions = ko.observable(parent.questions);

    this.getAmount = ko.computed(function() {
        return '$' + this.payment.amount();
    }, this);

    this.getTotalAmount = function(paymentCardFee) {
        var fee = 0;
        if (this.paymentSource.type() == 'card') {
            fee = this.payment.amount()*parseFloat(paymentCardFee)/100;
        }
        return '$'+(parseFloat(this.payment.amount()) + fee).toFixed(2);
    };

    this.getFeeAmountText = function(paymentCardFee) {
        return '$' + (this.payment.amount() * parseFloat(paymentCardFee) / 100).toFixed(2);
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
        if (self.paymentSource) {
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
        var currentStep = steps[current];
        switch (currentStep) {
            case 'details':
                break;
            case 'source':
                if (data.newAddress) {
                    addNewAddress(data.newAddress);
                }
                // Do not change order of next calls:
                self.paymentAccounts.push(data.paymentAccount);
                self.payment.paymentAccountId(data.paymentAccount.id);
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
                $('#pay-popup').dialog('close');
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

        var data = jQuery('#' + formId).serializeArray();

        jQuery.ajax({
            url: url,
            type: 'POST',
            timeout: 30000, // 30 secs
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
                    $('#' + formName + ' .error').removeClass('error');
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
        var currentStep = steps[current];
        switch (currentStep) {
            case 'details':
                sendData(Routing.generate('checkout_pay_payment'), forms[currentStep]);
                break;
            case 'source':
                if (!self.payment.paymentAccountId() && !self.newPaymentAccount()) {
                    window.formProcess.removeAllErrors('#pay-popup ');
                    window.formProcess.addFormError(
                        '#' + forms[currentStep],
                        Translator.get('payment_account.error.choice.empty')
                    );
                } else if (self.newPaymentAccount()) {
                    sendData(Routing.generate('checkout_pay_source'), forms[currentStep]);
                } else {
                    window.formProcess.removeAllErrors('#pay-popup ');
                    self.step(steps[++current]);
                }
                break;
            case 'user':
                self.isProcessQuestion = false;
                sendData(Routing.generate('checkout_pay_user'), forms[currentStep]);
                break;
            case 'questions':
                //User is valid and we have question so we can try process it
                if (self.isValidUser() && !self.isProcessQuestion) {
                    sendData(Routing.generate('experian_pidkiq_execute'), forms[currentStep]);
                    self.isProcessQuestion = true;
                //Wrong answer for question, but we have user and we can move to next step
                } else if(self.isValidUser() && self.isProcessQuestion) {
                    window.formProcess.removeAllErrors('#pay-popup ');
                    jQuery.each(forms, function(key, formName) {
                        $('#' + formName + ' .error').removeClass('error');
                    });
                    onSuccessStep([]);
                }
                //User is invalid so we don't do any think and live error
                break;
            case 'pay':
                sendData(Routing.generate('checkout_pay_exec'), forms['details']);
                break;
        }

    };

    this.previous = function() {
        current--;
        window.formProcess.removeAllErrors('#pay-popup ');
        this.step(steps[current]);
    };

    this.cancelDialog = function() {
        new Cancel(self.payment.id());
    };

    // Constructor

    if (contract.payment) {
        ko.mapping.fromJS(contract.payment, {}, this.payment);
    }

    $('#pay-popup').dialog({
        width: 650,
        modal: true,
        beforeClose: function( event, ui ) {
            self.paymentAccounts([{id: '', name: ''}]);
            self.newUserAddress([new Address()]);
            $("input.datepicker-field").datepicker("destroy");
        }
    });


    $('.ui-dialog>#pay-popup').css("top","0px");


    $("input.datepicker-field").datepicker({
        showOn: "both",
        buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
        buttonImageOnly: true,
        showOtherMonths: true,
        selectOtherMonths: true,
        dateFormat: 'm/d/yy',
        minDate: new Date()
    });

//    $("#vi-questions").parent().replaceWith($("#vi-questions"));
//    $('#vi-questions').slimScroll({
//        alwaysVisible:true,
//        width:330,
//        height:260
//    });

    $('.user-ssn').ssn();

    ko.applyBindings(this, $('#pay-popup').get(0));

    jQuery.each(forms, function(key, formName) {
        jsfv[formName].addError = window.formProcess.addFormError;
        jsfv[formName].removeErrors = function(field) {};
        jQuery('#' + formName).submit(function() {
            self.next();
            return false;
        });
    });

    window.formProcess.removeAllErrors('#pay-popup ');
}
