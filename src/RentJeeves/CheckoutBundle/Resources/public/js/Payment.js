function Payment(parent, paidTo) {
    var self = this;

    this.checkDueDate = function () {
        if (isNaN(self.startYear()) || isNaN(self.startMonth())) {
            return null;
        }
        var dayInMonth = Date.getDaysInMonth(self.startYear(), self.startMonth() - 1);

        if (dayInMonth >= self.dueDate()) {
            parent.infoMessage(null);
            return;
        }

        parent.infoMessage(
            Translator.trans(
                'info.payment.date',
                {
                    "%DAY_1%" : self.dueDate(),
                    "%DAY_2%" : dayInMonth
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
    this.type = ko.observable('recurring');

    this.frequency = ko.observable('monthly');
    this.frequency.subscribe(function(newValue) {
        if ('month_last_date' == newValue) {
            this.dueDate(31);
        } else {
            this.dueDate(startDate.getDate());
        }
    }, this);

    this.startMonth = ko.observable(paidTo.toString("M"));
    this.startYear = ko.observable(paidTo.toString("yyyy"));
    this.dueDate = ko.observable();
    this.dueDate.subscribe(function(newValue) {
        self.checkDueDate();
    });
    this.startYear.subscribe(function(newValue) {
        self.checkDueDate();
    });
    this.startMonth.subscribe(function(newValue) {
        self.checkDueDate();
    });
    this.dueDate(paidTo.toString("d"));
    this.startDate = ko.computed({
        read: function() {
            if (isNaN(self.startYear()) || isNaN(self.startMonth())) {
                return null;
            }
            var dayInMonth = Date.getDaysInMonth(self.startYear(), self.startMonth() - 1);

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
            self.startDate(paidTo.toString("M/d/yyyy"));
        }
        if ('recurring' == newValue) {
            self.dueDate(paidTo.toString("d"));
            self.startMonth(paidTo.toString("M"));
            self.startYear(paidTo.toString("yyyy"));
        }
    });

    this.ends = ko.observable('cancelled');

    this.endMonth = ko.observable(null);
    this.endYear = ko.observable(null);

    /**
     * Make sure month and year it integer, because library for work with date(datejs)
     * show such error: Uncaught TypeError: 2026 is not a Number.
     */
    this.endMonth.subscribe(function(newValue) {
        if (typeof newValue === 'number') {
            return;
        }
        self.endMonth(parseInt(newValue));
    });

    this.endYear.subscribe(function(newValue) {
        if (typeof newValue === 'number') {
            return;
        }
        self.endYear(parseInt(newValue));
    });
}
