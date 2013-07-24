function Contracts() {
  var limit = 10;
  var current = 1;
  var self = this;
  this.aContracts = ko.observableArray([]);
  this.pages = ko.observableArray([]);
  this.total = ko.observable(0);
  this.current = ko.observable(1);
  this.ajaxAction = function() {
    $.ajax({
      url: Routing.generate('landlord_contracts_list'),
      type: 'POST',
      dataType: 'json',
      data: {
        'data': {
          'page' : self.current(),
          'limit' : limit
        }
      },
      success: function(response) {
        self.aContracts([]);
        self.aContracts(response.contracts);
        self.total(response.total);
        self.pages(response.pagination);
      }
    });
  };
  this.countContracts = ko.computed(function(){
    return parseInt(self.aContracts().length);
  });
  this.goToPage = function(page) {
    self.current(page);
    if (page == 'First') {
      self.current(1);
    }
    if (page == 'Last') {
      self.current(Math.ceil(self.total()/limit));
    }
    self.ajaxAction();
  };
}

var ContractsViewModel = new Contracts();

$(document).ready(function(){
  ko.applyBindings(ContractsViewModel, $('#contracts-block').get(0));
  ContractsViewModel.ajaxAction();
});
