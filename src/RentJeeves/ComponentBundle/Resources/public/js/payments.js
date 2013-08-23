function Payments() {
  var limit = 10;
  var current = 1;
  var self = this;
  this.aPayments = ko.observableArray([]);
  this.pages = ko.observableArray([]);
  this.total = ko.observable(0);
  this.current = ko.observable(1);
  this.last = ko.observable('Last');
  this.processPayment = ko.observable(true);
  this.sortColumn = ko.observable("status");
  this.isSortAsc = ko.observable(true);
  this.searchText = ko.observable("");
  this.searchCollum = ko.observable("");
  this.isSearch = ko.observable(false);
  this.notHaveResult = ko.observable(false);

  this.search = function() {
    var searchCollum = $('#searchPayments').linkselect('val');
    if(typeof searchCollum != 'string') {
       searchCollum = '';
    }
    if(self.searchText().length <= 0) {
      $('#searsh-field-payments').css('border-color', 'red');
      return;
    } else {
      $('#searsh-field-payments').css('border-color', '#bdbdbd');
    }
    self.isSearch(true);
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
  this.ajaxAction = function() {
    self.processPayment(true);
    $.ajax({
      url: Routing.generate('landlord_payments_list'),
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
        self.processPayment(false);
        self.aPayments([]);
        self.aPayments(response.payments);
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

var PaymentsViewModel = new Payments();

$(document).ready(function(){
  ko.applyBindings(PaymentsViewModel, $('#payments-block').get(0));
  PaymentsViewModel.ajaxAction();
  $('#searchPayments').linkselect("destroy");
  $('#searchPayments').linkselect();
});
