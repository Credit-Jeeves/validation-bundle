function Properties() {
  var self = this;
  this.aProperties = ko.observableArray();

  this.ajaxAction = function(sAction, nTradelineId) {
    $.ajax({
      url: Routing.generate('landlord_properties_list'),
      type: 'POST',
      dataType: 'json',
      data: {
            },
      success: function(response) {
      }
    });
  };

  this.countProperties = ko.computed(function(){
    return parseInt(self.aProperties().length);
  });
  
}

function Units() {
  var self = this;
  this.aUnits = ko.observableArray();
  this.ajaxAction = function(sAction, nPropertyId) {
    $.ajax({
      url: Routing.generate('landlord_units_list'),
      type: 'POST',
      dataType: 'json',
      data: {'property_id': nPropertyId},
      success: function(response) {
        self.aUnits(response);
      }
    });
  };
  this.clearUnits = function() {
    self.aUnits([]);
  }
}

$(document).ready(function(){
  var UnitsViewModel = new Units();
  ko.applyBindings(UnitsViewModel, $('#units-block').get(0));
  
    $('#properties-block table tbody').delegate('.property-edit', 'click', function(){
      var nPropertyId = this.id.split('-')[1];
      UnitsViewModel.ajaxAction('unit', nPropertyId);
      return false;
    });
});
