function PayDatesComputing(parent) {
    var self = this;

    self.isDueDay = function (date) {
        if (-1 == parent.contract().groupSetting.dueDays.indexOf(date.getDate())) {
            return [false, ''];
        }
        return [true, ''];
    };

    self.getPaidFor = ko.computed(function () {
        if (parent.payment.paidFor()) {
            return (parent.paidForArr()[parent.payment.paidFor()] ||
            Translator.trans('checkout.rent_start.title.non_selected_month'));
        }

        return Translator.trans('checkout.rent_start.title.non_selected_month');
    });

    self.fullPayTo = ko.computed(function () {
        return parent.contract() ? parent.contract().payToName : '';
    });

    /**
     * @returns array which contains 2 fields 'bank' and 'card'
     */
    self.getBusinessDaysForCurrentPaymentProcessor = function () {
        if (parent.contract().groupSetting.payment_processor === 'heartland') {
            return document.bussinesDays.heartland;
        } else if (parent.contract().groupSetting.payment_processor === 'aci') {
            return document.bussinesDays['aci_' + parent.contract().groupSetting.orderAlgorithm];
        } else {
            return {card: 1, bank: 1};
        }
    };

    /**
     * using in AttributeGeneratorWeb, we can`t remove
     */
    self.settleDays = 0;

    /**
     * see BusinessDaysCalculator
     */
    self.shiftToNextBusinessDay = function (date) {
        switch (date.getDay()) {
            case 6:
                date.add(2).day();
                break;
            case 5:
                date.add(3).day();
                break;
            default:
                date.add(1).day();
                break;
        }
    };

    self.shiftBusinessDays = function (date, targetShift) {
        var businessDate = date;
        var shiftedDays = 0;

        // if date is Sat or Sun --> treat it as Mon
        switch (date.getDay()) {
            case 0: // Sun
                date.add(1).day();
                break;
            case 6: // Sat
                date.add(2).day();
                break;
        }

        while (shiftedDays < targetShift) {
            businessDate = self.shiftToNextBusinessDay(date);
            shiftedDays++;
        }
    };

    self.settle = ko.computed(function () {
        var shiftsBusinessDay = self.getBusinessDaysForCurrentPaymentProcessor();

        if (shiftsBusinessDay.bank === shiftsBusinessDay.card) {
            var settleDate = new Date(parent.payment.startDate());
            self.shiftBusinessDays(settleDate, shiftsBusinessDay.bank);

            return settleDate.toString('M/d/yyyy');
        } else { // https://credit.atlassian.net/browse/RT-1909
            var settleDateForBank = new Date(parent.payment.startDate());
            var settleDateForCard = new Date(parent.payment.startDate());
            self.shiftBusinessDays(settleDateForBank, shiftsBusinessDay.bank);
            self.shiftBusinessDays(settleDateForCard, shiftsBusinessDay.card);

            return Translator.trans('checkout.payment.settle_dates', {
                DATE_FOR_CARD: settleDateForCard.toString('M/d/yyyy'),
                DATE_FOR_BANK: settleDateForBank.toString('M/d/yyyy')
            });
        }
    });

    self.getLastPaymentDay = ko.computed(function () {
        var finishDate = new Date();
        finishDate.setDate(1);
        finishDate.setMonth(parent.payment.endMonth() - 1);
        finishDate.setYear(parent.payment.endYear());
        var daysInMonth = Date.getDaysInMonth(
            parseInt(parent.payment.endYear()), parseInt(parent.payment.endMonth()) - 1
        );
        finishDate.setDate(
            parent.payment.dueDate() > daysInMonth ? daysInMonth : parent.payment.dueDate()
        );
        return finishDate.toString('M/d/yyyy');
    });

    self.getChooseDateText = ko.computed(function () {
        if (parent && parent.contract) {
            var contract = ko.unwrap(parent.contract);
            if (contract && contract.paymentMinStartDate) {
                var minStartDate = new Date(contract.paymentMinStartDate);
                var now = new Date();
                if (minStartDate.valueOf() > now.valueOf()) {
                    return Translator.trans('checkout.payment.choose_date_limit.text');
                }
            }
        }

        return Translator.trans('checkout.payment.choose_date.text');
    });

    self.defaultMinStartDateString = ko.observable((new Date()).toString('M/d/yyyy'));

    self.getMinStartDate = function (contract) {
        var defaultMinStartDate = new Date(self.defaultMinStartDateString());
        contract = contract ? contract : parent.contract();
        if (contract && contract.paymentMinStartDate) {
            var minStartDate = new Date(contract.paymentMinStartDate);
            if (minStartDate.valueOf() > defaultMinStartDate.valueOf()) {
                return minStartDate.toString('M/d/yyyy');
            }
        }

        return defaultMinStartDate.toString('M/d/yyyy');
    };

    self.updateMinStartDate = function (contract) {
        $('.datepicker-field').trigger('updateOptions', {'minDate': self.getMinStartDate(contract)});
    };

    /**
     * @returns int
     */
    self.getMaxBusinessDaysForDelivery = function () {
        var businessDays = self.getBusinessDaysForCurrentPaymentProcessor();
        if (businessDays.bank > businessDays.card) {
            return businessDays.bank;
        } else {
            return businessDays.card;
        }
    }
}
