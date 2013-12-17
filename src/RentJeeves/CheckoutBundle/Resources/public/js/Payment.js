function Payment(parent, startDate) {
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

    this.dueDate = ko.observable(startDate.toString("d"));
    this.startMonth = ko.observable(startDate.toString("M"));
    this.startYear = ko.observable(startDate.toString("yyyy"));
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
            self.startDate(Date.today().toString("M/d/yyyy"));
        }
        if ('recurring' == newValue) {
            self.dueDate(startDate.toString("d"));
            self.startMonth(startDate.toString("M"));
            self.startYear(startDate.toString("yyyy"));
        }
    });

    this.ends = ko.observable('cancelled');
    this.endMonth = ko.observable(null);
    this.endYear = ko.observable(null);
}
