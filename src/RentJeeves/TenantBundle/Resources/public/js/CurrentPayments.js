function CurrentPayments(contracts, verification, paidForArr) {
    var self = this;

    self.verification = verification;

    self.getContractById = function(id) {
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

    self.getPaidForArrContractById = function(id) {
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

    self.openPayPopup = function(contractId) {
        if (!self.pay) {
            self.pay = new Pay(self, self.getContractById(contractId));
        } else {
            self.pay.contract(self.getContractById(contractId));
        }
    };

    self.openPayAnythingPopup = function(contractId) {
        if (!self.pay_anything) {
            self.pay_anything = new PayAnything(self, self.getContractById(contractId));
        } else {
            self.pay_anything.contract(self.getContractById(contractId));
        }
    };

    self.openPayAnythingCancelPopup = function(paymentId) {
        new Cancel(paymentId);
    };
}
