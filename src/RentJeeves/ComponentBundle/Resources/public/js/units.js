function Units() {
    var self = this;
    this.aUnits = ko.observableArray([]);
    this.errors = ko.observableArray([]);
    this.total = ko.observable(1);
    this.add = ko.observable(1);
    this.property = ko.observable(0);
    this.show = ko.observable(false);
    this.name = ko.observable();
    this.process = ko.observable(true);
    this.ajaxAction = function(nPropertyId) {
        self.errors([]);
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
    this.cancelEdit = function() {
      self.errors([]);
      $('#edit-property-popup').dialog('close');
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
      if (self.isValid()) {
        self.process(true);
        $.ajax({
          url: Routing.generate('landlord_units_save'),
          type: 'POST',
          dataType: 'json',
          data: {'units': self.aUnits(), 'property_id': self.property()},
          success: function(response) {
            self.clearUnits();
            PropertiesViewModel.ajaxAction();
            $('#edit-property-popup').dialog('close');
          }
        });
      }
    };
    this.isValid = function() {
      var names = {};
      var namesArray = [];
      var errors = {};
      var errorsArray = [];
      var valid = true;
      var units = self.aUnits();
      self.errors(errors);
      for (var i = 0; i < units.length; i++) {
        var unit = units[i];
          var tmp = unit.name;
          if (typeof names[tmp] == 'undefined' & typeof tmp != 'undefined') {
            names[tmp] = unit;
            namesArray.push(unit);
          } else {
            valid = false;
            errors[tmp] = unit;
            errorsArray.push('Unit #' + unit.name + ' already exists!');
          }
        }
      self.errors(errorsArray);
      return valid;
    };
    this.countErrors = ko.computed(function(){
      return parseInt(self.errors().length);
    });
    this.removeUnit = function(unit) {
        if (confirm(Translator.transChoice('remove.unit.confirm'))) {
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
