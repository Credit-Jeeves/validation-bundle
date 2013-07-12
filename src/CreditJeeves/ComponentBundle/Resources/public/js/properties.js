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
$(document).ready(function(){
});
