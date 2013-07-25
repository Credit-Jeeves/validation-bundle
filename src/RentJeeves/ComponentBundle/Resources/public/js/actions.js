function Actions() {
  var limit = 10;
  var current = 1;
  var self = this;
  this.aActions = ko.observableArray([]);
  this.pages = ko.observableArray([]);
  this.total = ko.observable(0);
  this.current = ko.observable(1);
  this.ajaxAction = function() {
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
  this.Resolve = function() {
    return false;
  };
}

var ActionsViewModel = new Actions();

$(document).ready(function(){
  ko.applyBindings(ActionsViewModel, $('#actions-block').get(0));
  ActionsViewModel.ajaxAction();
});
