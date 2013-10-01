function CurrentPayments(contracts, verification) {
    var self = this;

    this.verification = verification;
    this.questions = '';
    this.getContractById = function(id) {
        var contract = null;
        jQuery.each(contracts, function(key, val) {
            if (id == val.id) {
                contract = val;
                return true;
            }
        });
        return contract;
    };

    this.openPayPopup = function(contractId) {
        self.pay = new Pay(this, contractId);
    };
}
