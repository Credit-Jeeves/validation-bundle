function CurrentPayments(contracts, verification, paidForArr) {
    var self = this;

    this.verification = verification;
    this.questions = '';
    this.getContractById = function(id) {
        var contract = null;
        jQuery.each(contracts, function(key, val) {
            if (id == val.id) {
                contract = val;
                return false;
            }
            return true;
        });
        return contract;
    };
    this.getPaidForArrContractById = function(id) {
        var paidFor = null;
        jQuery.each(paidForArr, function(key, val) {
            if (id == key) {
                paidFor = val;
                return false;
            }
            return true;
        });
        return paidFor;
    };

    this.openPayPopup = function(contractId) {
        self.pay = new Pay(this, contractId);
    };
}
