function CurrentPayments() {
    this.openPayPopup = function(contractId) {
        new Pay(contractId);
    };
}
