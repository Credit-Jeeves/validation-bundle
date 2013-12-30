function BillingAccount(data) {
    this.id = ko.observable(null);
    this.nickname = ko.observable(null);
    this.routingNumber = ko.observable(null);
    this.accountNumber = ko.observable(null);
    this.accountType = ko.observable(null);
    this.isActive = ko.observable(false);

    ko.mapping.fromJS(data, {}, this);

    this.allowActive = !this.isActive();
}
