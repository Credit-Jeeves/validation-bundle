function TenantPaymentsViewModel()
{
    var self = this;
    var mapping = new TenantPaymentMapping();

    this.tenantPayments = ko.observableArray([]);
    this.pages = ko.observableArray([]);
    this.currentPage = ko.observable(1);
    this.isLoading = ko.observable(false);
    this.selectedContract = ko.observable(null);

    this.loadData = function()
    {
        this.isLoading(true);
        $('#tenant-payments').showOverlay();
        $.ajax({
            url: Routing.generate('tenant_payments', {'page': self.currentPage(), 'contractId': self.selectedContract()}),
            type: 'GET',
            timeout: 30000,
            dataType: 'json',
            success: function(data) {
                ko.mapping.fromJS(data, mapping, self);
                $('#tenant-payments').hideOverlay();
                $('.status-text-helper')
                    .tooltip({
                        items: 'span',
                        position: { my: 'left center', at: 'right+30 center' }
                    })
                    .off("mouseover")
                    .on("click", function () {
                        var message = $(this).prev().attr('title');
                        $(this).tooltip("option", "content", self.prepareMessage(message));
                        $(this).tooltip("open");

                        return false;
                    });
                self.isLoading(false);
            }
        });
    };

    this.filterPayments = function(li, value, text)
    {
        this.selectedContract(value);
        this.currentPage(1);
        this.loadData();
    };

    this.goToPage = function(value)
    {
        if (value != self.currentPage()) {
            self.currentPage(value);
            self.loadData();
        }
    };

    this.getStatusText = function(status)
    {
        return Translator.trans('order.status.text.' + status);
    };

    this.isNotSuccessStatus = function(status)
    {
        if (status != 'new' && status != 'complete' && status != 'pending') {
            return true;
        }

        return false;
    };

    this.prepareMessage = function(message)
    {
        if (message) {
            return Translator.trans('order.status.message', {"message" : message});
        }

        return Translator.trans('order.status.message.is_empty');
    };
}
