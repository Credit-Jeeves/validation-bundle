function PersonalInfoFillPidkiq(addresses) {
    this.newUserAddress = ko.observableArray([]);
    this.address = new Address(this, addresses);
}
