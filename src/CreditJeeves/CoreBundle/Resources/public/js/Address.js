function Address(parent, addresses, newAddress) {
    var self = this;

    if (!addresses) {
        addresses = ko.observableArray([]);
    }

    this.addresses = addresses;

    this.id = ko.observable(null);
    this.number = ko.observable(null);
    this.street = ko.observable('');
    this.city = ko.observable('');
    this.district = ko.observable(null);
    this.area = ko.observable(null);
    this.zip = ko.observable('');
    this.unit = ko.observable(null);

    this.toString = ko.computed(function() {
        return (this.number() ? this.number() + ' ' : '') + this.street() + ', ' +
            (this.district() ? this.district() + ', ' : '') +
            (this.unit() ? '#' + this.unit() + ' ' : '') + this.city() + ', ' +
            this.area() + ' ' + this.zip();
    }, self);

    this.addressChoice = ko.observable(null);
    this.addressChoice.subscribe(function(newValue) {
        if (null != newValue) {
            self.isAddNewAddress(false);
        }
    });

    this.isAddNewAddress = ko.observable(!self.addresses().length);
    this.addAddress = function() {
        self.isAddNewAddress(true);
        self.addressChoice(null);

        if (newAddress) {
            var isUnique = true;
            jQuery.each(self.addresses(), function(key, val) {
                if (val.toString() == newAddress.toString()) {
                    isUnique = false;
                    return false;
                }
                return true;
            });
            if (isUnique) {
                var number = ko.unwrap(newAddress.number);

                self.street((number ? number + ' ' : '') + ko.unwrap(newAddress.street));
                self.zip(ko.unwrap(newAddress.zip));
                self.area(ko.unwrap(newAddress.area));
                self.city(ko.unwrap(newAddress.city));
            }
        }
    };

    var findAddressById = function(id) {
        var address = null;
        jQuery.each(self.addresses(), function(key, val) {
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
