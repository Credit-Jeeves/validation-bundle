var Vehicles = {};

var vehicles = [];

Vehicles.app = {
start : function() {
  this.initMakeAndModel();
},
initMakeAndModel : function() {
  var onChange = function(event, make) {
    models = vehicles[make];
    console.log(models);
    var options = [];
    var key = 0;
    for (var model in models) {
      options.push(
          {
            value: key,
            text: model
          }
      );
      key++;
    }
      $('#creditjeeves_applicantbundle_leadnewtype_target_name_model').linkselect('replaceOptions', options);
    };
    $('#creditjeeves_applicantbundle_leadnewtype_target_name_make').linkselect({
      change : onChange
    });
  }
};

$(document).ready(function() {
  $('#creditjeeves_applicantbundle_leadnewtype_target_name_make').linkselect('destroy');
  Vehicles.app.start();
});
