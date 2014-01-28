if (modelSelector === undefined) {
    var modelSelector = '#creditjeeves_applicantbundle_leadnewtype_target_name_model';
}
if (makeNameSelector === undefined) {
    var makeNameSelector = '#creditjeeves_applicantbundle_leadnewtype_target_name_make';
}
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
      $(modelSelector).linkselect('replaceOptions', options);
    };
    $(makeNameSelector).linkselect({
      change : onChange
    });
  }
};

$(document).ready(function() {
  $(makeNameSelector).linkselect('destroy');
  Vehicles.app.start();
});
