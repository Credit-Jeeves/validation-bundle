function CreditTrackPayDialog(options) {
    this.root = $('#credit-track-pay-popup');

    ko.cleanNode(this.root.get(0));

    this.paymentAccounts = ko.observableArray($.map(this.root.data('paymentAccounts'), function(value) {
      return [value];
    }));
    this.paymentGroup = this.root.data('paymentGroup');

    var self = this;
    var current = 0;
    this.infoMessage = ko.observable(null);

    this.getCurrentStep = function() {
        return steps[current];
    }

    this.previous = function() {
        window.formProcess.removeAllErrors('#credit-track-pay-popup');
        current--;
        this.step(steps[current]);
    };

    var forms = {
        'source': 'rentjeeves_checkoutbundle_paymentaccounttype',
        'details': 'rentjeeves_checkoutbundle_paymenttype'
    };

    var steps = ['source', 'pay'];

    this.passedSteps = ko.observableArray([]);

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
    });

    this.step('source');

    this.propertyFullAddress = new Address(this, window.addressesViewModels);

    this.propertyAddress = ko.observable(this.propertyFullAddress.toString());

    var now = new Date();
    this.payment = new Payment(this, now);
    this.payment.amount(options.amount);
    this.payment.dueDate(now.getDate());

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

    this.getTotalAmount = function() {
        return Format.money(this.payment.amount(), 'USD');
    };

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

    this.paymentSource = new PaymentSource(this, false, this.propertyFullAddress);
    this.paymentSource.groupId(this.paymentGroup.id);

    this.getLastPaymentDay = 'no finish date';

    this.address = new Address(this, window.addressesViewModels, this.propertyFullAddress);

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
            case 'pay':
                $('#credit-track-pay-popup').dialog('close');
                jQuery('body').showOverlay();
                window.location.reload();
                return;
                break;
        }

        self.step(steps[++current]);
    };

    var sendData = function(url, formId) {
        jQuery('#credit-track-pay-popup').showOverlay();

        var data = jQuery('#' + formId).serializeArray();

        jQuery.ajax({
            url: url,
            type: 'POST',
            timeout: 30000, // 30 secs
            dataType: 'json',
            data: jQuery.param(data, false),
            error: function(jqXHR, textStatus, errorThrown) {
                window.formProcess.removeAllErrors('#credit-track-pay-popup ');
                jQuery('#credit-track-pay-popup').hideOverlay();
                window.formProcess.reLogin(jqXHR, errorThrown);
                window.formProcess.addFormError('#' + formId, errorThrown);
            },
            success: function(data, textStatus, jqXHR) {
                window.formProcess.removeAllErrors('#credit-track-pay-popup ');
                jQuery.each(forms, function(key, formName) {
                    $('#' + formName + ' .error').removeClass('error');
                });

                jQuery('#credit-track-pay-popup').hideOverlay();
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
            case 'source':
                if (!self.payment.paymentAccountId() && !self.newPaymentAccount()) {
                    window.formProcess.removeAllErrors('#credit-track-pay-popup ');
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
            case 'pay':
                sendData(Routing.generate('credittrack_pay_exec'), forms['source']);
                break;
        }

    };

    this.cancelDialog = function() {
        new Cancel(self.payment.id());
    };

    ko.applyBindings(this, $('#credit-track-pay-popup').get(0));

    $('#credit-track-pay-popup').dialog({
        width: 650,
        modal: true,
        beforeClose: function( event, ui ) {
            self.paymentAccounts([{id: '', name: ''}]);
            self.newUserAddress([new Address()]);
        }
    });

    window.formProcess.removeAllErrors('#credit-track-pay-popup');
}

