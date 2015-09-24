function BatchedDeposit(data) {
    this.depositDate = ko.observable(null);
    this.batchNumber = ko.observable(null);
    this.orderAmount = ko.observable(null);
    this.depositType = ko.observable(null);
    this.orders = ko.observableArray([]);

    ko.mapping.fromJS(data, {}, this);
}
