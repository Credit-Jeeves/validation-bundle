function Payment(parent, paidTo) {
    var self = this;
    this.id = ko.observable(null);
    this.contractId = null;
    this.paymentAccountId = ko.observable(null);
    this.amount = ko.observable(null);
    this.type = ko.observable('recurring');

    this.frequency = ko.observable('monthly');
    this.frequency.subscribe(function(newValue) {
        if ('month_last_date' == newValue) {
            this.dueDate(31);
        } else {
            this.dueDate(startDate.getDate());
        }
    }, this);

    this.dueDate = ko.observable(paidTo.toString("d"));
    this.startMonth = ko.observable(paidTo.toString("M"));
    this.startYear = ko.observable(paidTo.toString("yyyy"));
    this.startDate = ko.computed({
        read: function() {
            return this.startMonth() + '/' + this.dueDate() + '/' + this.startYear();
        },
        write: function (value) {
            var date = Date.parseExact(value,  "M/d/yyyy");
            this.startMonth(date.toString('M'));
            this.startYear(date.toString('yyyy'));
            this.dueDate(date.toString('d'));
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
}
