function Contracts() {
  var self = this;
  
};
var ContractsViewModel = new Contracts();
$(document).ready(function(){
  ko.applyBindings(TenantsViewModel, $('#contracts-history').get(0));
});