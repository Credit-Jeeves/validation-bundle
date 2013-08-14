function ContractDetails() {
  var self = this;
  this.contract = ko.observable();
  this.approve = ko.observable(false);
  this.review = ko.observable(false);
  this.edit = ko.observable(false);
  this.invite = ko.observable(false);
  this.due = ko.observableArray(['1th', '5th', '10th', '15th', '20th', '25th']);
  this.cancelEdit = function(data)
  {
    $('#tenant-edit-property-popup').dialog('close');
    if(self.approve()) {
      self.approveContract(self.contract());
    }
    self.clearDetails();
  }

  this.editContract = function(data) {
    $('#tenant-approve-property-popup').dialog('close');
    $('#tenant-edit-property-popup').dialog('open');
    if (data.first_name) {
      self.contract(data);
    }
    if(self.approve()) {
      var flag = true;
    } else {
      var flag = false;
    }
    self.clearDetails();
    self.edit(true);
    self.approve(flag);
    window.jQuery.curCSS = window.jQuery.css;
    $('#contractEditStart').datepicker({
      showOn: "button",
      buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png", 
      format:'m/d/Y',
      date: $('#contractEditStart').val(),
      current: $('#contractEditStart').val(),
      starts: 1,
      position: 'r',
      onBeforeShow: function(){
        $('#contractEditStart').DatePickerSetDate($('#contract-edit-start').val(), true);
      },
      onChange: function(formated, dates){
        $('#contractEditStart').val(formated);
        $('#contractEditStart').DatePickerHide();
      }
    });
    $('#contractEditFinish').datepicker({
      showOn: "button",
      buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
      format:'m/d/Y',
      date: $('#contractEditFinish').val(),
      current: $('#contractEditFinish').val(),
      starts: 1,
      position: 'r',
      onBeforeShow: function(){
        $('#contractEditFinish').DatePickerSetDate($('#contract-edit-finish').val(), true);
      },
      onChange: function(formated, dates){
        $('#contractEditFinish').val(formated);
        $('#contractEditFinish').DatePickerHide();
      }
    });
  };
  this.approveContract = function(data) {
    $('#tenant-approve-property-popup').dialog('open');
    self.clearDetails();
    self.contract(data);
    self.approve(true);
  };
  this.reviewContract = function(data) {
    $('#tenant-review-property-popup').dialog('open');
    self.clearDetails();
    self.approve(false);
    self.contract(data);
    self.review(true);
  };
  this.approveSave = function() {
    $('#tenant-approve-property-popup').dialog('close');
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
    $('#tenant-edit-property-popup').dialog('close');
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
  this.sort = ko.observable('ASC');
  this.sortColumn = ko.observable("status");
  this.isSortAsc = ko.observable(true);
  this.searchText = ko.observable("");
  this.searchCollum = ko.observable("");
  this.isSearch = ko.observable(false);
  this.notHaveResult = ko.observable(false);
  this.processLoading = ko.observable(true);

  this.search = function() {
    var searchCollum = $('#searchFilter').linkselect('val');
    if(typeof searchCollum != 'string') {
       searchCollum = '';
    }
    if(self.searchText().length <= 0) {
      $('#search').css('border-color', 'red');
      return;
    } else {
      $('#search').css('border-color', '#bdbdbd');
    }
    self.isSearch(true);
    self.searchText(self.searchText());
    self.searchCollum(searchCollum);
    self.current(1);
    self.ajaxAction();
  }

  this.clearSearch = function() {
    self.searchText('');
    self.searchCollum('');
    self.current(1);
    self.ajaxAction();
    self.isSearch(false);
  }


  this.sortFunction = function(data, event) {
     field = event.target.id;

     if(field.length == 0) {
        return;
     }
     self.sortColumn(field);
     $('.sort-dn').attr('class', 'sort');
     $('.sort-up').attr('class', 'sort');
     if(self.isSortAsc() === false) {
      self.isSortAsc(true);
      $('#'.field).attr('class', 'sort-dn');
     } else {
      self.isSortAsc(false);
      $('#'.field).attr('class', 'sort-up');
     }
     
     self.current(1);
     self.ajaxAction();
  };

  this.ajaxAction = function() {
    self.aContracts([]);
    self.processLoading(true);
    $.ajax({
      url: Routing.generate('landlord_contracts_list'),
      type: 'POST',
      dataType: 'json',
      data: {
        'data': {
          'page' : self.current(),
          'limit' : limit,
          'sortColumn': self.sortColumn(),
          'isSortAsc': self.isSortAsc(),
          'searchCollum': self.searchCollum(),
          'searchText': self.searchText()
        }
      },
      success: function(response) {
        self.processLoading(false);
        self.aContracts([]);
        self.aContracts(response.contracts);
        self.total(response.total);
        self.pages(response.pagination);
        if(self.countContracts() <= 0) {
           self.notHaveResult(true);
        } else {
           self.notHaveResult(false);
        }
        if(self.sortColumn().length == 0) {
          return;
        }
        if(self.isSortAsc()) {
          $('#'+self.sortColumn()).attr('class', 'sort-dn');
        } else {
          $('#'+self.sortColumn()).attr('class', 'sort-up');
        }
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
    DetailsViewModel.approveContract(data);
  };
  this.reviewContract = function(data) {
    var position = $('#edit-' + data.id).position();
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
  $('#tenant-approve-property-popup').dialog({ 
      autoOpen: false,
      resizable: false,
      modal: true,
      width:'520px'
  });
  $('#tenant-edit-property-popup').dialog({
      autoOpen: false,
      resizable: false,
      modal: true,
      width:'520px'
  });

  $('#tenant-review-property-popup').dialog({
      autoOpen: false,
      resizable: false,
      modal: true,
      width:'520px'
  });

  ContractsViewModel.ajaxAction();
  $('#searchFilter').linkselect("destroy");
  $('#searchFilter').linkselect();
});
