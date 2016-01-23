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

    self.settleDays = function() {
        if(parent.contract().groupSetting.payment_processor === 'heartland'){
            return document.bussinesDays.heartland;
        } else if(parent.contract().groupSetting.payment_processor === 'aci') {
            if(parent.contract().groupSetting.orderAlgorithm === 'pay_direct'){
                return document.bussinesDays.aci_pay_direct;
            } else {
                return document.bussinesDays.aci_submerchant;
            }
        } else {
            return 1;
        }
    };

    self.settle = ko.computed(function() {
        var settleDate = new Date(parent.payment.startDate());
        var startDayOfWeek = (0 == settleDate.getDay() ? 7 : settleDate.getDay()); // Move Sunday from 0 to 7
        /* logic: skip weekends */
        var daysAdd = (4 == startDayOfWeek || 6 == startDayOfWeek ? 1 : 0);
        if (0 == daysAdd) {
            daysAdd = (5 == startDayOfWeek ? 2 : 0);
        }
        /* end of logic: skip weekends */
        settleDate.add(self.settleDays()).days();// see comment of this.settleDays
        var dayOfWeek = (0 == settleDate.getDay() ? 7 : settleDate.getDay()); // Move Sunday from 0 to 7
        var daysShift = 8 - dayOfWeek; // Settle day can't be weekend
        if (2 < daysShift) {
            daysShift = 0;
        }
        settleDate.add(daysShift + daysAdd).days();
        return settleDate.toString('M/d/yyyy');
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
