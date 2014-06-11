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
                self.isLoading(false);
            }
        });
    };

    this.filterPayments = function(li, value, text)
    {
        this.selectedContract(value);
        this.currentPage(1);
        this.loadData();
    }

    this.goToPage = function(value)
    {
        if (value != self.currentPage()) {
            self.currentPage(value);
            self.loadData();
        }
    }

    this.getStatusText = function(status)
    {
        return Translator.trans('order.status.text.' + status);
    }
}
