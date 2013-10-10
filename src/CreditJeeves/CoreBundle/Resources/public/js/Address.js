function Address(parent, addresses, newAddress) {
    var self = this;

    if (!addresses) {
        addresses = [];
    }

//    this.addresses = ko.observableArray(addresses);

    this.id = ko.observable(null);
    this.street = ko.observable('');
    this.city = ko.observable('');
    this.area = ko.observable(null);
    this.zip = ko.observable('');

    this.toString = ko.computed(function() {
        return this.street() + ', ' + this.city() + ', ' + this.area() + ' ' + this.zip();
    }, this);

    if (newAddress) {
        var isUnique = true;
        jQuery.each(addresses, function(key, val) {
            if (val.toString() == newAddress.toString()) {
                isUnique = false;
                return false;
            }
            return true;
        });
        if (isUnique) {
            self.street(ko.unwrap(newAddress.street));
            self.zip(ko.unwrap(newAddress.zip));
            self.area(ko.unwrap(newAddress.area));
            self.city(ko.unwrap(newAddress.city));
        }
    }

    this.addressChoice = ko.observable(null);
    this.addressChoice.subscribe(function(newValue) {
        if (null != newValue) {
            self.isAddNewAddress(false);
        }
    });

    this.isAddNewAddress = ko.observable(!addresses.length);
    this.addAddress = function() {
        self.isAddNewAddress(true);
        self.addressChoice(null);
    };

    var findAddressById = function(id) {
        var address = null;
        jQuery.each(addresses, function(key, val) {
            if (val.id() == id) {
                address = val;
                return false;
            }
            return true;
        });
        return address;
    };

    this.current = ko.computed(function() {
        if (self.isAddNewAddress()) {
            return self.toString();
        } else if (selected = self.addressChoice()) {
            if (address = findAddressById(selected)) {
                return address.toString()
            }
        }
        return '';
    }, this);

    this.clear = function() {
        self.street('');
        self.city('');
        self.area(null);
        self.zip('');
        self.addressChoice(null);
    };
}
