function Resolve() {
  var self = this;
  this.details = ko.observable();
  this.openForm = function(data) {
    $('#contract-resolve').dialog('open');
    self.details(data);
  };
  this.closeForm = function() {
    $('#contract-resolve').dialog('close');
  };
  this.resolve = function() {
    $.ajax({
      url: Routing.generate('landlord_conflict_resolve'),
      type: 'POST',
      dataType: 'json',
      data: {
          'contract_id' : self.details().id,
          'action' : $('input[name=ResolutionOptions]:checked').attr('title')
      },
      success: function() {
        ActionsViewModel.ajaxAction();
        self.closeForm();
      }
    });
  };
};

function Actions() {
  var limit = 10;
  var current = 1;
  var self = this;
  this.aActions = ko.observableArray([]);
  this.pages = ko.observableArray([]);
  this.total = ko.observable(0);
  this.current = ko.observable(1);
  this.processActions = ko.observable(true);
  this.sortColumn = ko.observable("statusA");
  this.isSortAsc = ko.observable(false);
  this.searchText = ko.observable("");
  this.searchCollum = ko.observable("");
  this.isSearch = ko.observable(false);
  this.notHaveResult = ko.observable(false);

  this.search = function() {
    var searchCollum = $('#searchActions').linkselect('val');
    if(typeof searchCollum != 'string') {
       searchCollum = '';
    }
    if(self.searchText().length <= 0) {
      $('#searsh-field-actions').css('border-color', 'red');
      return;
    } else {
      $('#searsh-field-actions').css('border-color', '#bdbdbd');
    }
    self.isSearch(true);
    self.searchCollum(searchCollum);
    self.current(1);
    self.ajaxAction();
  };

  this.clearSearch = function() {
    self.searchText('');
    self.searchCollum('');
    self.current(1);
    self.ajaxAction();
    self.isSearch(false);
  };

  this.ajaxAction = function() {
	self.processActions(true);
    self.aActions([]);
    $.ajax({
      url: Routing.generate('landlord_actions_list'),
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
        $('#actions-block').show();
        self.processActions(false);
        self.aActions([]);
        self.aActions(response.actions);
        self.total(response.total);
        self.pages(response.pagination);
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
  this.countActions = ko.computed(function(){
    return parseInt(self.aActions().length);
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
  this.Resolve = function(data) {
    ResolveViewModel.openForm(data);
  };
  this.sortIt = function(data, event) {
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
}

var ActionsViewModel = new Actions();
var ResolveViewModel = new Resolve();

$(document).ready(function(){
  ko.applyBindings(ActionsViewModel, $('#actions-block').get(0));
  ko.applyBindings(ResolveViewModel, $('#contract-resolve').get(0));
  ActionsViewModel.ajaxAction();
  $('#contract-resolve').dialog({ 
    autoOpen: false,
    resizable: false,
    modal: true,
    width:'520px'
});  
});
