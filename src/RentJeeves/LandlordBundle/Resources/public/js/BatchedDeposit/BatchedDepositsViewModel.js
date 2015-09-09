function BatchedDepositsViewModel()
{
    var self = this;
    var mapping = new BatchedDepositsMapping();

    var limit = 10;
    
    this.deposits = ko.observableArray([]);
    this.pages = ko.observableArray([]);
    this.pagination = ko.observableArray([]);

    this.total = ko.observable(0);
    this.current = ko.observable(1);
    this.last = ko.observable('Last');
    this.isLoading = ko.observable(false);
    this.filter = ko.observable('');

    this.loadData = function() {
        self.deposits([]);
        self.isLoading(true);
        
        $.ajax({
            url: Routing.generate('landlord_deposits_list'),
            type: 'POST',
            dataType: 'json',
            data: {
                'page' : self.current(),
                'limit' : limit,
                'filter': self.filter()
            },
            success: function(data) {
                ko.mapping.fromJS(data, mapping, self);
                self.isLoading(false);
            }
        });
    };

    this.goToPage = function(page) {
        if (page !== self.current()) {
            self.current(page);
            self.loadData();
        }
    };

    this.togglePayments = function(deposit) {
        if ($('.toggled-' + deposit.batchNumber()).first().is(':visible')) {
            $('.toggled-' + deposit.batchNumber()).hide();
            $('#title-show-' + deposit.batchNumber()).show();
            $('#title-hide-' + deposit.batchNumber()).hide();
        } else {
            $('.toggled-' + deposit.batchNumber()).show();
            $('#title-show-' + deposit.batchNumber()).hide();
            $('#title-hide-' + deposit.batchNumber()).show();
        }
    };

    this.toggledZebraCss = function(batchNumber, index, isRoot) {
        var cssClass =  '';
        if (!isRoot) {
            cssClass += 'toggled-' + batchNumber;
        }
        if (index%2 == 0) {
            cssClass += ' zebra-tr-dark';
        }

        return cssClass;
    };

    this.depositTitle = function(deposit) {
        var amount = deposit.orders().length;
        return Translator.transChoice('payments.batched_amount', amount, {"count": amount});
    };

    this.orderStatusTitle = function(order) {
        if (this.isSuccessfulStatus(order.status)) {
            return Translator.trans('landlord_dashboard.payment.title', {"created": order.start, "sent": order.depositDate});
        }

        return order.errorMessage;
    };

    this.getOrderStatusText = function(order) {
        return Translator.trans(order.status());
    };

    this.isSuccessfulStatus = function(status)
    {
        if (status == 'order.status.text.new' ||
            status == 'order.status.text.complete' ||
            status == 'order.status.text.pending' ||
            status == 'order.status.text.sending') {
            return true;
        }

        return false;
    };

    this.getTitle = function(title)
    {
        return title.charAt(0).toUpperCase() + title.slice(1);
    }
}
