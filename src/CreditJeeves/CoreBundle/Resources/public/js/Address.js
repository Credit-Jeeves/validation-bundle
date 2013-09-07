function Address(parent) {
    this.street = ko.observable('');
    this.city = ko.observable('');
    this.area = ko.observable(null);
    this.zip = ko.observable('');

    this.toString = ko.computed(function(){
        var address = this.street() + ' ' + this.city() + ', ' + this.area() + ' ' + this.zip();

        return address;
    }, this);
}
