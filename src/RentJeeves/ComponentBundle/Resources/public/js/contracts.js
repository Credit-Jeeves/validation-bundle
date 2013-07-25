function ContractDetails() {
  var self = this;
  this.contract = ko.observable();
  this.show = ko.observable(false);
  this.marginTop = ko.observable(0);
  this.details = function(data) {
    console.log(data.top);
    self.marginTop(data.top + 'px');
    self.contract(data);
    self.show(true);
  };
  this.clearDetails = function(){
    self.show(false);
  };
  this.saveContract = function(){
    
  };
}

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
  this.editContract = function(data) {
    var position = $('#edit-' + data.id).position();
    data.top = position.top - 300;
    DetailsViewModel.details(data);
  };
}

var ContractsViewModel = new Contracts();
var DetailsViewModel = new ContractDetails();

$(document).ready(function(){
  ko.applyBindings(ContractsViewModel, $('#contracts-block').get(0));
  ko.applyBindings(DetailsViewModel, $('#contract-details').get(0));
  ContractsViewModel.ajaxAction();
});
