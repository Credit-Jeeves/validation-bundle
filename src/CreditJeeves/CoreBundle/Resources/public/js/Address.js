function Address(parent, addresses, newAddress) {
    self = this;

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
        return this.street() + ' ' + this.city() + ', ' + this.area() + ' ' + this.zip();
    }, this);

    if (newAddress) {
        var isUnique = true;
        jQuery.each(addresses, function(key, val) {
            if (self.toString() == newAddress.toString()) {
                isUnique = false;
                return true;
            }
            return false;
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
            console.log('addressChoice');
            self.isAddNewAddress(false);
        }
    });

    this.isAddNewAddress = ko.observable(!addresses.length);
    this.addAddress = function() {
        console.log('addAddress');
        self.isAddNewAddress(true);
        self.addressChoice(null);
        console.log(self.isAddNewAddress());
        console.log(self.addressChoice());
    };

    var findAddressById = function(id) {
        var address = null;
        jQuery.each(addresses, function(key, val) {
            console.log(val.id() + ' == ' + id);
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
}
