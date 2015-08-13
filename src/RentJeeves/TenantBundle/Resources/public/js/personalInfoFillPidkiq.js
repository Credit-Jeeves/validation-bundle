function PersonalInfoFillPidkiq(addresses) {
    var self = this;

    self.addresses = ko.observableArray([]);
    /**
     * Mapping addresses data that was retrieved from server
     *
     * @param addresses
     */
    self.mapAddresses = function(addresses) {
        self.addresses.removeAll();
        var mappedArray = jQuery.map(addresses, function(addressData) {
            var addressModel = new Address(self, self.addresses);
            ko.mapping.fromJS(addressData, {}, addressModel);
            return addressModel;
        });
        self.addresses(mappedArray);
    };

    self.address = new Address(self, self.addresses);

    self.newAddresses = ko.observableArray([]);

    self.mapAddresses(addresses);
}
