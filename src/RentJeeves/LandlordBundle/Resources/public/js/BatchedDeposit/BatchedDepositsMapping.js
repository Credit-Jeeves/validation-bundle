function BatchedDepositsMapping() {
    this.deposits = {
        create: function(options) {
            return ko.observable(new BatchedDeposit(options.data));
        }
    };
    this.pages = {
        create: function(options) {
            var result = [];
            for (var i = 1; i <= options.data; i++) {
                result[i] = i;
            }
            return result;
        }
    }
}
