function PaymentAccountSettings(data) {
    var self = this;

    this.allowPaymentSourceTypes = {
        'bank' : ko.observable(false),
        'card' : ko.observable(false),
        'debit_card' : ko.observable(false)
    };
    this.feeSettings = {
        'bank' : {'type' : ko.observable('flat_fee'), 'value' : ko.observable(0.00)},
        'card' : {'type' : ko.observable('percentage'), 'value' : ko.observable(0.00)},
        'debit_card' : {'type' : ko.observable('percentage'), 'value' : ko.observable(0.00)}
    };

    var mapping = {
        'settings': {
            create: function(options) {
                var settings = options.data;

                if (typeof settings.allowBank != 'undefined') {
                    if (typeof settings.allowBank == 'function') {
                        options.parent.allowPaymentSourceTypes['bank'] = settings.allowBank;
                    } else {
                        options.parent.allowPaymentSourceTypes['bank'](settings.allowBank);
                    }
                }

                if (typeof settings.allowCreditCard != 'undefined') {
                    if (typeof settings.allowCreditCard == 'function') {
                        options.parent.allowPaymentSourceTypes['card'] = settings.allowCreditCard;
                    } else {
                        options.parent.allowPaymentSourceTypes['card'](settings.allowCreditCard);
                    }
                }

                if (typeof settings.allowDebitCard != 'undefined') {
                    if (typeof settings.allowDebitCard == 'function') {
                        options.parent.allowPaymentSourceTypes['debit_card'] = settings.allowDebitCard;
                    } else {
                        options.parent.allowPaymentSourceTypes['debit_card'](settings.allowDebitCard);
                    }
                }

                if (typeof settings.feeDC != 'undefined') {
                    if (typeof settings.feeDC == 'function') {
                        options.parent.feeSettings['debit_card'].value = settings.feeDC;
                    } else {
                        options.parent.feeSettings['debit_card'].value(settings.feeDC);
                    }
                }

                if (typeof settings.typeFeeDC != 'undefined') {
                    if (typeof settings.typeFeeDC == 'function') {
                        options.parent.feeSettings['debit_card'].type = settings.typeFeeDC;
                    } else {
                        options.parent.feeSettings['debit_card'].type(settings.typeFeeDC);
                    }
                }

                if (typeof settings.feeCC != 'undefined') {
                    if (typeof settings.feeCC == 'function') {
                        options.parent.feeSettings['card'].value = settings.feeCC;
                    } else {
                        options.parent.feeSettings['card'].value(settings.feeCC);
                    }
                }

                if (typeof settings.feeACH != 'undefined' && typeof settings.isPassedACH != 'undefined') {
                    if (typeof settings.isPassedACH == 'function') {
                        options.parent.feeSettings['bank'].value = ko.computed(function () {
                            var isPassedACH = ko.unwrap(settings.isPassedACH);
                            if (typeof settings.feeACH == 'function') {
                                var feeACH = ko.unwrap(settings.feeACH);
                                return isPassedACH ? feeACH : 0.00;
                            } else {
                                return isPassedACH ? settings.feeACH : 0.00;
                            }
                        });

                    } else {
                        if (typeof settings.feeACH == 'function' && settings.isPassedACH) {
                            options.parent.feeSettings['bank'].value = settings.feeACH;
                        } else {
                            options.parent.feeSettings['bank'].value(settings.isPassedACH  ? settings.feeACH : 0);
                        }
                    }
                }

                delete options.parent.settings;
            }
        }
    };

    ko.mapping.fromJS(data, mapping, self);

    this.clear = function () {
        self.allowPaymentSourceTypes({});
        self.feeSettings({});
    }
}
