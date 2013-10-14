function Payment(parent, startDate) {
    var self = this;

    this.id = ko.observable(null);
    this.contractId = null;
    this.paymentAccountId = ko.observable(null);
    this.amount = ko.observable(null);
    this.type = ko.observable('recurring');
    this.type.subscribe(function(newValue) {
        if ('one_time' == newValue) {
            self.ends('cancelled');
        }
    });
    this.frequency = ko.observable('monthly');
    this.frequency.subscribe(function(newValue) {
        if ('month_last_date' == newValue) {
            this.dueDate(31);
        } else {
            this.dueDate(startDate.getDate());
        }
    }, this);
    this.dueDate = ko.observable(startDate.getDate());
    this.startMonth = ko.observable(startDate.getMonth());
    this.startYear = ko.observable(startDate.getYear());
    this.startDate = ko.computed(function() {
        return this.startMonth() + '/' + this.dueDate() + '/' + this.startYear();
    }, self);
    this.ends = ko.observable('cancelled');
    this.endMonth = ko.observable(null);
    this.endYear = ko.observable(null);


}
