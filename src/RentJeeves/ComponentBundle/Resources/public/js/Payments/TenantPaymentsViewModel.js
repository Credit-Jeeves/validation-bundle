function TenantPaymentsViewModel(data)
{
    var self = this;

    this.tenantPayments = ko.observableArray([]);
    var mapping = new TenantPaymentMapping();
    ko.mapping.fromJS(data.payments, mapping.payments, this.tenantPayments);
}
