/**
 * @param parent
 * @param defaultAddress
 */
function PayAddress(parent, defaultAddress) {
    var self = this;

    self.addresses = ko.observableArray([]);

    self.newAddresses = ko.observableArray([]);

    if (defaultAddress) {
        defaultAddress.subscribe(function (newDefaultAddress) {
            self.address = new Address(self, self.addresses, newDefaultAddress);
            self.billingaddress = new Address(self, self.addresses, newDefaultAddress);
        });

        // first time init
        self.address = new Address(self, self.addresses, defaultAddress());
        self.billingaddress = new Address(self, self.addresses, defaultAddress());
    } else {
        self.address = new Address(self, self.addresses);
        self.billingaddress = new Address(self, self.addresses);
    }

    var beforeMapAddressesHandler = function (owner) {
        if (typeof (parent.beforeMapAddressesHandler) === 'function') {
            parent.beforeMapAddressesHandler(parent);
        }
    };

    var afterMapAddressesHandler = function (owner) {
        if (typeof (parent.afterMapAddressesHandler) === 'function') {
            parent.afterMapAddressesHandler(parent);
        }
        self.address.isAddNewAddress(!self.addresses().length);
        self.billingaddress.isAddNewAddress(!self.addresses().length);
    };

    /**
     * Mapping addresses data that was retrieved from server
     *
     * @param addresses
     */
    self.mapAddresses = function(addresses) {
        beforeMapAddressesHandler();
        self.addresses.removeAll();
        var mappedArray = jQuery.map(addresses, function(addressData) {
            var addressModel = new Address(self, self.addresses);
            ko.mapping.fromJS(addressData, {}, addressModel);
            return addressModel;
        });
        self.addresses(mappedArray);
        afterMapAddressesHandler();
    };

    self.addNewAddress = function(newAddressData) {
        var address = new Address(self);
        ko.mapping.fromJS(newAddressData, {}, address);

        self.addresses.push(address);
        self.newAddresses.push(address);

        self.address.clear();
        self.billingaddress.clear();

        self.address.addressChoice(newAddressData.id);
        self.billingaddress.addressChoice(newAddressData.id);

        afterMapAddressesHandler();
    };

    /**
     * Load initialization data from server
     */
    self.load = function() {
        // Retrieve payment accounts from server by contract
        jQuery.getJSON(
            Routing.generate('tenant_addresses_list'),
            function(data) {
                self.mapAddresses(data);
            }
        );
    };

    /**
     * Init object
     */
    self.init = function() {
        self.load();
    };

    // Construct

    self.init();
}
