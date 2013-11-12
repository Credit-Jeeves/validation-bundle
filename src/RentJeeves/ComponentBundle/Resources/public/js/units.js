function Units() {
    var self = this;
    this.aUnits = ko.observableArray([]);
    this.total = ko.observable(1);
    this.add = ko.observable(1);
    this.property = ko.observable(0);
    this.show = ko.observable(false);
    this.name = ko.observable();
    this.process = ko.observable(true);
    this.ajaxAction = function(nPropertyId) {
        $('#edit-property-popup').dialog('open');
        self.property(nPropertyId);
        self.process(true);
        $.ajax({
            url: Routing.generate('landlord_units_list'),
            type: 'POST',
            dataType: 'json',
            data: {'property_id': nPropertyId},
            success: function(response) {
                self.name(response.property);
                self.aUnits(response.units);
                self.total(response.units.length);
                self.show(true);
                self.process(false);
            }
        });
    };
    this.clearUnits = function() {
        self.aUnits([]);
        self.total(0);
        self.add(1);
        self.property(0);
        self.show(false);
    };
    this.addUnits = function() {
        for(var i=0; i < self.add(); i++) {
            self.aUnits.push({'name': '', 'id': ''});
        }
        var count = parseInt(self.total());
        count += parseInt(self.add());
        self.total(count);
    };
    this.saveUnits = function() {
        $('#edit-property-popup').dialog('close');
        $.ajax({
            url: Routing.generate('landlord_units_save'),
            type: 'POST',
            dataType: 'json',
            data: {'units': self.aUnits(), 'property_id': self.property()},
            success: function(response) {
                self.clearUnits();
                PropertiesViewModel.ajaxAction();
            }
        });
    };
    this.removeUnit = function(unit) {
        if (confirm('Are you sure?')) {
            self.aUnits.remove(unit);
        }
    };
    this.deletePropertyConfirm = function()
    {
        $('#edit-property-popup').dialog('close');
        removeProperty.show();
    };

    this.deleteProperty = function() {
        $.ajax({
            url: Routing.generate('landlord_property_delete'),
            type: 'POST',
            dataType: 'json',
            data: {'property_id': self.property()},
            success: function(response) {
                self.clearUnits();
                PropertiesViewModel.ajaxAction();
            }
        });
    };
}