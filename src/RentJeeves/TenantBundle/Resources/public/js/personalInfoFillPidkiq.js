function PersonalInfoFillPidkiq() {
    this.isAddNewAddress = ko.observable(false);
    this.newUserAddress = ko.observableArray([]);
    this.address = new Address(this, window.addressesViewModels);
}