function TenantPayment(data) {
    this.status = ko.observable(null);
    this.errorMessage = ko.observable(null);
    this.style = ko.observable(null);
    this.date = ko.observable(null);
    this.property = ko.observable(null);
    this.rent = ko.observable(null);
    this.other = ko.observable(null);
    this.total = ko.observable(null);
    this.type = ko.observable(null);
    this.lastFour = ko.observable(null);

    ko.mapping.fromJS(data, {}, this);
}
