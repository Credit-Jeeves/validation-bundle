function removeProperty()
{
    var self = this;
    this.aUnits = ko.observableArray([]);
    this.name = ko.observable('gg');
    this.countUnit = ko.observable('333');
    this.property = ko.observable(null);
    this.show = function(){
        self.aUnits([]);
        $('#remove-property-popup').dialog('open');
        self.aUnits(UnitsViewModel.aUnits());
        self.name(UnitsViewModel.name());
        self.countUnit(UnitsViewModel.aUnits().length);
    };

    this.deleteProperty = function()
    {
        $('#remove-property-popup').dialog('close');
        $.ajax({
            url: Routing.generate('landlord_property_delete'),
            type: 'POST',
            dataType: 'json',
            data: {'property_id': self.property().id},
            success: function(response) {
                PropertiesViewModel.ajaxAction();
            }
        });
    };

    this.cancel = function()
    {
        $('#remove-property-popup').dialog('close');
    };

    this.showStandalone = function(property) {
        self.property(property);
        $('#remove-property-popup').dialog('open');
        self.aUnits([]);
        self.name(property.address);
        self.countUnit(0);
    }
}

