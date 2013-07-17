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
  this.aUnits = ko.observableArray();
  this.ajaxAction = function(sAction, nTradelineId) {
    $.ajax({
      url: Routing.generate('landlord_units_list'),
      type: 'POST',
      dataType: 'json',
      data: {
            },
      success: function(response) {
      }
    });
  };
  
}

$(document).ready(function(){
  var UnitsViewModel = new Units();
  ko.applyBindings(UnitsViewModel, $('#units-block').get(0));
  
    $('#properties-block table tbody').delegate('.property-edit', 'click', function(){
      var nUnitId = this.id.split('-')[1];
      UnitsViewModel.ajaxAction('unit', nUnitId);
      return false;
    });
});
