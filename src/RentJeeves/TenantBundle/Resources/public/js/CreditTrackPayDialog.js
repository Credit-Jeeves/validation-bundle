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
    };

    this.previous = function() {
        window.formProcess.removeAllErrors('#credit-track-pay-popup');
        current--;
        this.step(steps[current]);
    };

    var forms = {
        'source': 'rentjeeves_checkoutbundle_paymentaccounttype'
    };

    var steps = ['source', 'pay'];

    this.passedSteps = ko.observableArray([]);

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
    });

    this.step('source');

    var now = new Date();

    self.propertyAddress = ko.computed(function() {
        return new Address(self, self.addresses);
    });

    ko.utils.extend(self, new PayAddress(self, self.propertyAddress));

    // Connected Payment Source Component
    // Component should be connected after contractId and disableCreditCard and before it should be using
    ko.utils.extend(self, new PaymentSourceViewModel(self, null, false, 'card'));

    self.changePaymentAccountHandler = function() {
        self.billingaddress.addressChoice(self.currentPaymentAccount().addressId());
    };

    jQuery('#rentjeeves_checkoutbundle_paymentaccounttype_type_row').hide(); //Temporary #RT-529
    this.getLastPaymentDay = 'no finish date';

    this.stepExist = function(step) {
        return -1 != steps.indexOf(step);
    };

    var onSuccessStep = function(data) {
        var currentStep = self.getCurrentStep();
        switch (currentStep) {
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
                $('#credit-track-pay-popup').dialog('close');
                jQuery('body').showOverlay();
                window.location.href = data.url;
                return;
                break;
        }

        self.step(steps[++current]);
    };

    var sendData = function(url, formId) {
        jQuery('#credit-track-pay-popup').showOverlay();

        var data = jQuery('#' + formId).serializeArray();

        data.push({
            'name': 'group_id',
            'value': self.paymentGroup.id
        });

        jQuery.ajax({
            url: url,
            type: 'POST',
            timeout: 60000, // 30 secs
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
                if (!self.currentPaymentAccountId() && !self.isNewPaymentAccount()) {
                    window.formProcess.removeAllErrors('#credit-track-pay-popup ');
                    window.formProcess.addFormError(
                        '#' + forms[currentStep],
                        Translator.trans('payment_account.error.choice.empty')
                    );
                } else if (self.isNewPaymentAccount()) {
                    sendData(Routing.generate('checkout_pay_source'), forms[currentStep]);
                } else {
                    sendData(Routing.generate('checkout_pay_existing_source'), forms[currentStep]);
                }
                break;
            case 'pay':
                sendData(Routing.generate('credittrack_pay_exec'), forms['source']);
                break;
        }

    };

    self.prepareDialog = function () {
        $('#credit-track-pay-popup').dialog({ // TODO replace by knockout custom handler
            width: 650,
            modal: true,
            beforeClose: function( event, ui ) {
                current = 0;
                self.step(steps[current]);
                self.currentPaymentAccountId(null);
                self.currentPaymentAccount().clear();
                if (self.paymentAccounts().length > 0) {
                    self.isNewPaymentAccount(false);
                }
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

        window.formProcess.removeAllErrors('#credit-track-pay-popup');
    };

    ko.applyBindings(this, $('#credit-track-pay-popup').get(0));

    this.prepareDialog();
}

