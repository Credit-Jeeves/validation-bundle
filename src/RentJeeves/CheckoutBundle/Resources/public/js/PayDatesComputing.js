function PayDatesComputing(parent) {
    var self = this;

    self.isDueDay = function(date) {
        if (-1 == parent.contract().groupSetting.dueDays.indexOf(date.getDate())) {
            return [false, ''];
        }
        return [true, ''];
    };

    self.getPaidFor = ko.computed(function() {
        if (parent.payment.paidFor()) {
            return (parent.paidForArr()[parent.payment.paidFor()] ||
                Translator.trans('checkout.rent_start.title.non_selected_month'));
        }

        return Translator.trans('checkout.rent_start.title.non_selected_month');
    });

    self.fullPayTo = ko.computed(function() {
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
     * @returns int using in AttributeGeneratorWeb
     */
    self.settleDays = function () {
        var bussinesDays = self.getBusinessDaysForCurrentPaymentProcessor();

        return (bussinesDays.bank > bussinesDays.card) ? bussinesDays.bank : bussinesDays.card;
    };

    self.settle = ko.computed(function () {
        var settleDate = new Date(parent.payment.startDate());
        var startDayOfWeek = (0 == settleDate.getDay() ? 7 : settleDate.getDay()); // Move Sunday from 0 to 7
        /* logic: skip weekends */
        var daysAdd = (4 == startDayOfWeek || 6 == startDayOfWeek ? 1 : 0);
        if (0 == daysAdd) {
            daysAdd = (5 == startDayOfWeek ? 2 : 0);
        }
        /* end of logic: skip weekends */

        var businessDays = self.getBusinessDaysForCurrentPaymentProcessor();
        settleDate.add(businessDays.bank).days();
        var dayOfWeek = (0 == settleDate.getDay() ? 7 : settleDate.getDay()); // Move Sunday from 0 to 7
        var daysShift = 8 - dayOfWeek; // Settle day can't be weekend
        if (2 < daysShift) {
            daysShift = 0;
        }
        settleDate.add(daysShift + daysAdd).days();

        if (businessDays.bank === businessDays.card) {
            return settleDate.toString('M/d/yyyy');
        } else { // https://credit.atlassian.net/browse/RT-1909
            var settleDateForCard = new Date(parent.payment.startDate());
            settleDateForCard.add(businessDays.card).days();
            var dayOfWeekForCard = (0 == settleDateForCard.getDay() ? 7 : settleDateForCard.getDay());
            var daysShiftForCard = 8 - dayOfWeekForCard;
            if (2 < daysShiftForCard) {
                daysShiftForCard = 0;
            }
            settleDateForCard.add(daysShiftForCard + daysAdd).days();

            return settleDateForCard.toString('M/d/yyyy') + ' for Credit or <br/>' + settleDate.toString('M/d/yyyy') + ' for e-Check';
        }
    });

    self.getLastPaymentDay = ko.computed(function() {
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
}
