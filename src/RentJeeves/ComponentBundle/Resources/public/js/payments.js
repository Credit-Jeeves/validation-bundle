function Payments() {
  var limit = 10;
  var current = 1;
  var self = this;
  this.aPayments = ko.observableArray([]);
  this.pages = ko.observableArray([]);
  this.total = ko.observable(0);
  this.current = ko.observable(1);
  this.ajaxAction = function() {
    $.ajax({
      url: Routing.generate('landlord_payments_list'),
      type: 'POST',
      dataType: 'json',
      data: {
        'data': {
          'page' : self.current(),
          'limit' : limit
        }
      },
      success: function(response) {
        self.aPaymnets([]);
        self.aPayments(response.payments);
        self.total(response.total);
        self.pages(response.pagination);
      }
    });
  };
  this.countPayments = ko.computed(function(){
    return parseInt(self.aPayments().length);
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

var PaymentsViewModel = new Payments();

$(document).ready(function(){
  ko.applyBindings(PaymentsViewModel, $('#payments-block').get(0));
  PaymentsViewModel.ajaxAction();
});
