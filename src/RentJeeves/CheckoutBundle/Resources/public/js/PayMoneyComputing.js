/**
 * Pay Should be Parent
 * @param parent
 * @param contract
 */
function PayMoneyComputing(parent, contract) {
    var self = this;

    self.contract = contract;

    self.total = ko.computed(function() { // It will display to user
        if (self.contract().groupSetting.pay_balance_only) {
            return self.contract().integrated_balance;
        }

        return total = (parent.payment.amount()?parseFloat(parent.payment.amount()):0) +
            (parent.payment.amountOther()?parseFloat(parent.payment.amountOther()):0);
    });

    self.totalInput = ko.computed(function() { // It will be put into the hidden input
        if (!parent.payment.amount() && !parent.payment.amountOther()) {
            return null;
        }
        return self.total();
    });

    self.getAmount = ko.computed(function() {
        if (self.contract().groupSetting.pay_balance_only) {
            return Format.money(self.contract().integrated_balance);
        } else {
            return Format.money(parent.payment.amount());
        }
    });

    self.getOtherAmount = ko.computed(function() {
        return Format.money(parent.payment.amountOther());
    });

    self.getTotal = ko.computed(function() {
        return Format.money(self.total());
    });

    var feeCalculation = function(isText, type) {
        var fee = null;
        if ('card' == type) {
            fee = parseFloat(self.contract().groupSettings.feeCC);
            if (isText) {
                fee += '%'
            }
        } else if ('bank' == type) {
            if (self.contract().groupSettings.isPassedACH) {
                fee = parseFloat(self.contract().groupSettings.feeACH);
            } else {
                fee = 0;
            }
            if (isText) {
                fee = Format.money(fee);
            }
        } else if ('debit_card' == type) {
            fee = parseFloat(self.contract().groupSettings.feeDC);
            var feeType = self.contract().groupSettings.typeFeeDC;
            if (isText && 'percentage' == feeType) {
                fee += '%';
            } else if (isText) {
                fee = Format.money(fee);
            }
        }

        return fee ? fee : '$0.00';
    };

    self.feeDisplay = function (type) {
        return feeCalculation(true, type);
    };

    self.getFee = ko.computed(function() {
        return feeCalculation(false, parent.currentPaymentAccount().type());
    });

    self.getFeeText = ko.computed(function() {
        return feeCalculation(true, parent.currentPaymentAccount().type());
    });

    self.getFeeNote = ko.computed(function() {
        var i18nKey = null;
        var type = parent.currentPaymentAccount().type();
        if ('card' == type) {
            i18nKey = 'checkout.fee.card.note-%FEE%';
        } else if ('bank' == type) {
            i18nKey = 'checkout.fee.bank.note-%FEE%';
        } else if ('debit_card' == type) {
            i18nKey = 'checkout.fee.debit_card.note-%FEE%';
        }

        return i18nKey ? Translator.trans(i18nKey, {'FEE': feeCalculation(true, type)}) : '';
    });

    self.getFeeNoteHelp = ko.computed(function() {
        var i18nKey = null;
        var type = parent.currentPaymentAccount().type();
        if ('card' == type) {
            i18nKey = 'checkout.fee.card.note.help-%FEE%';
        } else if ('bank' == type) {
            i18nKey = 'checkout.fee.bank.note.help-%FEE%';
        } else if ('debit_card' == type) {
            i18nKey = 'checkout.fee.debit_card.note.help-%FEE%';
        }
        return i18nKey ? Translator.trans(i18nKey, {'FEE': feeCalculation(true, type)}) : '';
    });

    self.getFeeAmount = function(isText) {
        var fee = 0.00;
        var type = parent.currentPaymentAccount().type();
        if ('card' == type) {
            fee = parseFloat(self.contract().groupSettings.feeCC) / 100 * self.total();
        } else if ('bank' == type && self.contract().groupSettings.isPassedACH == true) {
            fee = parseFloat(self.contract().groupSettings.feeACH);
        } else if ('debit_card' == type) {
            var feeType = self.contract().groupSettings.typeFeeDC;
            if ('percentage' == feeType) {
                fee = parseFloat(self.contract().groupSettings.feeDC) / 100 * self.total();
            } else {
                fee = parseFloat(self.contract().groupSettings.feeDC);
            }
        }
        if (isText) {
            fee = Format.money(fee);
        }
        return fee;
    };

    self.getFeeAmountText = ko.computed(function() {
        return self.getFeeAmount(true);
    });

    self.getTotalWithFee = ko.computed(function() {
        var fee = self.getFeeAmount();
        return Format.money(parseFloat(self.total()) + fee);
    });
}
