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
  this.ajaxAction = function() {
    self.processActions(true);
    $.ajax({
      url: Routing.generate('landlord_actions_list'),
      type: 'POST',
      dataType: 'json',
      data: {
        'data': {
          'page' : self.current(),
          'limit' : limit,
          'sortColumn': self.sortColumn(),
          'isSortAsc': self.isSortAsc()
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
  this.Resolve = function() {
    return false;
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

$(document).ready(function(){
  ko.applyBindings(ActionsViewModel, $('#actions-block').get(0));
  ActionsViewModel.ajaxAction();
});
