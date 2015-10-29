function addProperties()
{
    this.property = ko.observable("");
    this.aUnits = ko.observableArray([]);
    this.add = ko.observable(1);
    this.google = ko.observable("");
    this.isSingle = ko.observable(false);
    this.singlePropertyErrorMessage = ko.observable('');

    var self = this;
    this.clearUnits = function() {
        self.aUnits([]);
        self.add(1);
    };

    this.addUnits = function() {
        if($('#addUnitToNewProperty').hasClass("grey")) {
            return;
        }
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
        if (self.aUnits().length == 0 && !self.isSingle()) {
            self.singlePropertyErrorMessage(Translator.trans('units.error.add_or_mark_single'));
            return;
        }

        var stringAddress = $('#property-search').val();

        self.singlePropertyErrorMessage('');
        jQuery.ajax({
            url: Routing.generate('landlord_property_add'),
            type: 'POST',
            dataType: 'json',
            data: {
                'data': JSON.stringify(self.google().data, null),
                'isSingle': self.isSingle(),
                'stringAddress': stringAddress,
                'addGroup': true
            },
            error: function(data) {
                var content = JSON.parse(data.responseText);
                self.singlePropertyErrorMessage(content.message);
            },
            success: function(data, textStatus, jqXHR) {
                $('#add-property-popup').dialog('close');
                self.isSingle(false);
                self.property().processProperty(true);
                var propertyId = data.property.id;
                if(propertyId) {
                    return self.saveUnits(propertyId);
                }

                alert('Something wrong, we can\'t save property');
            }
        });
    };

    this.removeUnit = function(unit) {
        if (confirm(Translator.trans('remove.unit.confirm'))) {
            self.aUnits.remove(unit);
        }
    };

    $('.single-property-checkbox input[type=checkbox]').click(function(){
        if ($('#property-units').is(":visible") === true) {
            $('#property-units').hide();
            $('.unit-name').remove();
            $('.units-item').remove();
            $('#numberOfUnit').val('');
            $('#unitCount').val(0);
        } else {
            $('#property-units').show();
        }
    });
}
