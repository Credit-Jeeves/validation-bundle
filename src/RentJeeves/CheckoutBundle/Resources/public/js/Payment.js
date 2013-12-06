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
            /**
             * It's hack for js knokout because it's don't want work well as we need
             * and if we use format with number 0, as example 08,07
             * it's never change
             */
            var month = parseInt(this.startMonth());
            var day = parseInt(this.dueDate());

            if (this.startMonth() < 10) {
                var month = '0'+this.startMonth();
            }
            if (this.dueDate() < 10) {
                var day = '0'+this.dueDate();
            }

            var date =  month + '/' + day + '/' + this.startYear();

            return date;
        },
        write: function (value) {
            var date = Date.parseExact(value,  "mm/dd/yyyy");
            this.startMonth(date.toString('m'));
            this.startYear(date.toString('yyyy'));
            this.dueDate(date.toString('d'));
        },
        owner: this
    });

    this.type.subscribe(function(newValue) {
        if ('one_time' == newValue) {
            self.ends('cancelled');
            self.startDate(Date.today().toString("MM/dd/yyyy"));
        }
        if ('recurring' == newValue) {
            self.dueDate(startDate.toString("dd"));
            self.startMonth(startDate.toString("MM"));
            self.startYear(startDate.toString("yyyy"));
        }
    });

    this.ends = ko.observable('cancelled');
    this.endMonth = ko.observable(null);
    this.endYear = ko.observable(null);
}
