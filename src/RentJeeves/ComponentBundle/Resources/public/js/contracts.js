function ContractDetails() {
  var self = this;
  this.contract = ko.observable();
  this.approve = ko.observable(false);
  this.review = ko.observable(false);
  this.edit = ko.observable(false);
  this.invite = ko.observable(false);
  this.marginTop = ko.observable(0);
  this.due = ko.observableArray(['1th', '5th', '10th', '15th', '20th', '25th']);
  this.editContract = function(data) {
    self.clearDetails();
    self.approve(false);
    if (data.top != undefined) {
      self.marginTop(data.top);
      self.contract(data);
    }
    self.edit(true);
    window.jQuery.curCSS = window.jQuery.css;
    $('#contract-edit-start').DatePicker({
      format:'m/d/Y',
      date: $('#contract-edit-start').val(),
      current: $('#contract-edit-start').val(),
      starts: 1,
      position: 'r',
      onBeforeShow: function(){
        $('#contract-edit-start').DatePickerSetDate($('#contract-edit-start').val(), true);
      },
      onChange: function(formated, dates){
        $('#contract-edit-start').val(formated);
        $('#contract-edit-start').DatePickerHide();
      }
    });  
    $('#contract-edit-finish').DatePicker({
      format:'m/d/Y',
      date: $('#contract-edit-finish').val(),
      current: $('#contract-edit-finish').val(),
      starts: 1,
      position: 'r',
      onBeforeShow: function(){
        $('#contract-edit-finish').DatePickerSetDate($('#contract-edit-finish').val(), true);
      },
      onChange: function(formated, dates){
        $('#contract-edit-finish').val(formated);
        $('#contract-edit-finish').DatePickerHide();
      }
    });
  };
  this.approveContract = function(data) {
    self.clearDetails();
    self.approve(false);
    if (data.top != undefined) {
      self.marginTop(data.top);
      self.contract(data);
    }
    self.approve(true);
  };
  this.reviewContract = function(data) {
    self.clearDetails();
    self.approve(false);
    if (data.top != undefined) {
      self.marginTop(data.top);
      self.contract(data);
    }
    self.review(true);
  };
  this.approveSave = function() {
    var data = self.contract();
    data.status = 'approved';
    self.contract(data);
    self.saveContract();
  };
  this.removeTenant = function() {
    var data = self.contract();
    data.action = 'remove';
    self.contract(data);
    self.saveContract();
  };
  this.clearDetails = function(){
    self.edit(false);
    self.review(false);
    self.approve(false);
  };
  this.saveContract = function(){
    var contract = self.contract();
    contract.finish = $('#contract-edit-finish').val() || contract.finish;
    contract.start = $('#contract-edit-start').val() || contract.start;
    self.contract(contract);
    $.ajax({
      url: Routing.generate('landlord_contract_save'),
      type: 'POST',
      dataType: 'json',
      data: {
        'contract': self.contract()
      },
      success: function(response) {
        self.clearDetails();
        ContractsViewModel.ajaxAction();
      }
    });
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
    DetailsViewModel.editContract(data);
  };
  this.approveContract = function(data) {
    var position = $('#edit-' + data.id).position();
    data.top = position.top - 300;
    DetailsViewModel.approveContract(data);
  };
  this.reviewContract = function(data) {
    var position = $('#edit-' + data.id).position();
    data.top = position.top - 300;
    DetailsViewModel.reviewContract(data);
  };
  this.addTenant = function() {
    
  };
  this.filterAddress = function(data) {
    console.log(data.id);
  };
}

var ContractsViewModel = new Contracts();
var DetailsViewModel = new ContractDetails();

$(document).ready(function(){
  ko.applyBindings(ContractsViewModel, $('#contracts-block').get(0));
  ko.applyBindings(DetailsViewModel, $('#contract-actions').get(0));
  ContractsViewModel.ajaxAction();
  
  
  
  
  
});
