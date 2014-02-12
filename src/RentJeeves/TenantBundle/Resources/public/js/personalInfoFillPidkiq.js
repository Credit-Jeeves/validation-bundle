function PersonalInfoFillPidkiq() {
    this.newUserAddress = ko.observableArray([]);
    this.address = new Address(this, window.addressesViewModels);
    if (window.defaultAddress) {
        this.address.addressChoice(window.defaultAddress);
    } else {
        this.address.isAddNewAddress(true);
    }
}
