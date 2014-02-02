function addProperties()
{
    this.property = ko.observable("");
    this.aUnits = ko.observableArray([]);
    this.add = ko.observable(1);
    this.google = ko.observable("");
    var self = this;
    this.clearUnits = function() {
        self.aUnits([]);
        self.add(1);
    };

    this.addUnits = function() {
        for(var i=0; i < self.add(); i++) {
            self.aUnits.push({'name': '', 'id': ''});
        }
    };

    this.saveUnits = function(propertyId) {
        $.ajax({
            url: Routing.generate('landlord_units_save'),
            type: 'POST',
            dataType: 'json',
            data: {'units': self.aUnits(), 'property_id': propertyId },
            success: function(response) {
                self.clearUnits();
                self.property().ajaxAction();
                $('#property-search').val(' ');
                markAsNotValid();
            }
        });
    };

    this.saveProperty = function() {
        if($('#saveProperty').hasClass("grey")) {
            return;
        }

        self.property().processProperty(true);
        $('#add-property-popup').dialog('close');

        jQuery.ajax({
            url: Routing.generate('landlord_property_add'),
            type: 'POST',
            dataType: 'json',
            data: {'data': JSON.stringify(self.google().data, null)},
            error: function(jqXHR, errorThrown, textStatus) {;
            },
            success: function(data, textStatus, jqXHR) {
                var propertyId = data.property.id;
                if(propertyId) {
                    return self.saveUnits(propertyId);
                }

                alert('Something wrong, we can\'t save property');
            }
        });
    };

    this.removeUnit = function(unit) {
        if (confirm(Translator.transChoice('remove.unit.confirm'))) {
            self.aUnits.remove(unit);
        }
    };
}
