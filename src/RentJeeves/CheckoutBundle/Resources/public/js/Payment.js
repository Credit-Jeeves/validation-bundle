function Payment(parent) {
    var self = this;

    this.checkDueDate = function () {
        if (!self.startYear() || !self.startMonth() || !self.dueDate()) {
            return null;
        }
        var dayInMonth = Date.getDaysInMonth(parseInt(self.startYear()), parseInt(self.startMonth()) - 1);
        //We don't need show info message for pay balance only, because we don't use dueDate field
        if (dayInMonth >= self.dueDate() || parent.contract.groupSetting.pay_balance_only) {
            parent.infoMessage(null);
            return;
        }

        parent.infoMessage(
            Translator.trans(
                'info.payment.date',
                {
                    'DAY_1' : self.dueDate(),
                    'DAY_2' : dayInMonth
                }
            )
        );
    };

    this.id = ko.observable(null);
    this.contractId = null;
    this.paymentAccountId = ko.observable(null);
    this.amount = ko.observable(null);
    this.paidForOptions = ko.observableArray(null);
    this.paidFor = ko.observable(null);
    this.amountOther = ko.observable(null);
    if (!parent.contract.groupSetting.pay_balance_only) {
        this.type = ko.observable('recurring');
    } else {
        this.type = ko.observable('one_time');
    }

    this.frequency = ko.observable('monthly');
    this.frequency.subscribe(function(newValue) {
        if ('month_last_date' == newValue) {
            this.dueDate(31);
        } else {
            this.dueDate(parent.contract.dueDate);
        }
    }, this);

    this.startMonth = ko.observable('');
    this.startMonths = ko.observableArray([]);
    this.startYear = ko.observable('');
    this.startYears = ko.observableArray([]);
    this.dueDate = ko.observable();
    this.dueDates = ko.observable([]);
    this.dueDate.subscribe(function(newValue) {
        self.checkDueDate();
    });
    this.startYear.subscribe(function(newValue) {
        self.checkDueDate();
    });
    this.startMonth.subscribe(function(newValue) {
        self.checkDueDate();
    });
    this.dueDate(null);
    this.startDate = ko.computed({
        read: function() {
            if (!self.startYear() || !self.startMonth() || !self.dueDate()) {
                return null;
            }
            var dayInMonth = Date.getDaysInMonth(parseInt(self.startYear()), parseInt(self.startMonth()) - 1);

            if (dayInMonth >= self.dueDate()) {
                return this.startMonth() + '/' + this.dueDate() + '/' + this.startYear();
            }

            return this.startMonth() + '/' + dayInMonth + '/' + this.startYear();
        },
        write: function (value) {
            var date = Date.parseExact(value,  "M/d/yyyy");
            if (!date) {
                return;
            }

            this.startMonth(date.getMonth()+1);
            this.startYear(date.getFullYear());
            this.dueDate(date.getDate());
        },
        owner: this
    });


    this.type.subscribe(function(newValue) {
        if ('one_time' == newValue) {
            self.ends('cancelled');
            //self.startDate(null);
            self.dueDate(null);
            self.startMonth(null);
            self.startYear(null);
        }
        if ('recurring' == newValue) {
            self.dueDate(null);
            self.startMonth(null);
            self.startYear(null);
        }
    });

    this.ends = ko.observable('cancelled');

    this.endMonth = ko.observable(null);
    this.endYear = ko.observable(null);
}
