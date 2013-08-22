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
  this.ajaxAction = function() {
    self.aActions([]);
    $.ajax({
      url: Routing.generate('landlord_actions_list'),
      type: 'POST',
      dataType: 'json',
      data: {
        'data': {
          'page' : self.current(),
          'limit' : limit
        }
      },
      success: function(response) {
        $('#actions-block').show();
        self.aActions([]);
        self.aActions(response.actions);
        self.total(response.total);
        self.pages(response.pagination);
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
