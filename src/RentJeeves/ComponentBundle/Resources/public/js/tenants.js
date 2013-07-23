function Tenants() {
  var limit = 10;
  var current = 1;
  var self = this;
  this.aTenants = ko.observableArray([]);
  this.pages = ko.observableArray([]);
  this.total = ko.observable(0);
  this.current = ko.observable(1);
  this.ajaxAction = function() {
    $.ajax({
      url: Routing.generate('landlord_tenants_list'),
      type: 'POST',
      dataType: 'json',
      data: {
        'data': {
          'page' : self.current(),
          'limit' : limit
        }
      },
      success: function(response) {
        self.aTenants([]);
        self.aTenants(response.tenants);
        self.total(response.total);
        self.pages(response.pagination);
      }
    });
  };
  this.countTenants = ko.computed(function(){
    return parseInt(self.aTenants().length);
  });
  this.goToPage = function(page) {
    self.current(page);
    if (page == 'first') {
      self.current(1);
    }
    if (page == 'last') {
      self.current(Math.ceil(self.total()/limit));
    }
    self.ajaxAction();
  };
}

var TenantsViewModel = new Tenants();

$(document).ready(function(){
  ko.applyBindings(TenantsViewModel, $('#tenants-block').get(0));
  TenantsViewModel.ajaxAction();
});
