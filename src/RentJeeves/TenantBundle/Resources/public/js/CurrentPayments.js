function CurrentPayments(contracts) {

    this.getContractById = function(id) {
        var contract = null;
        jQuery.each(contracts, function(key, val) {
            if (id == val.id) {
                contract = val;
                return true;
            }
        });
        return contract;
    }

    this.openPayPopup = function(contractId) {
        new Pay(this, contractId);
    };
}
