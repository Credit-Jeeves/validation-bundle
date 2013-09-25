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
        var address = this.street() + ' ' + this.city() + ', ' + this.area() + ' ' + this.zip();

        return address;
    }, this);


    var bindAddress = function(address, model) {
        model.street(ko.unwrap(address.street));
        model.zip(ko.unwrap(address.zip));
        model.area(ko.unwrap(address.area));
        model.city(ko.unwrap(address.city));
    };

    if (newAddress) {
        bindAddress(newAddress, this);
    }



    this.addressChoice = ko.observable(null);
    this.isAddNewAddress = ko.observable(!addresses.length);
    this.addAddress = function() {
        self.isAddNewAddress(true);
        self.addressChoice(null);
        bindAddress(newAddress, self);
    };

    var findAddressById = function(id) {
        var address = null;
        jQuery.each(addresses, function(key, val) {
            if (id == val.id()) {
                address = val;
                return true;
            }
        });
        return address;
    };

    this.addressChoice.subscribe(function(newValue) {
        if (null != newValue) {
            self.isAddNewAddress(false);
            if (address = findAddressById(newValue)) {
                bindAddress(address, self);
            }
        }
    });
}
