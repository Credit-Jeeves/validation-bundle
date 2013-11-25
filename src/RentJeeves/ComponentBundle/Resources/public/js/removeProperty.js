function removeProperty()
{
    var self = this;
    this.aUnits = ko.observableArray([]);
    this.name = ko.observable('gg');
    this.countUnit = ko.observable('333');
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
        UnitsViewModel.deleteProperty();
    };

    this.cancel = function()
    {
        $('#remove-property-popup').dialog('close');
        $('#edit-property-popup').dialog('open');
    };
}