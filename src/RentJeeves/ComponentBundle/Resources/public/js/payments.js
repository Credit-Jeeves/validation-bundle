function Payments() {
  var limit = 10;
  var current = 1;
  var self = this;
  this.payments = ko.observableArray([]);
  this.deposits = ko.observableArray([]);
  this.pages = ko.observableArray([]);
  this.total = ko.observable(0);
  this.current = ko.observable(1);
  this.last = ko.observable('Last');
  this.processPayment = ko.observable(true);
  this.sortColumn = ko.observable("date-initiated");
  this.isSortAsc = ko.observable(false);
  this.searchText = ko.observable("");
  this.searchCollum = ko.observable("status");
  this.notHaveResult = ko.observable(false);

  this.searchCollum.subscribe(function(newValue) {
      if (newValue == 'deposit') {
          self.filterDeposits();
      }
  });

  this.ajaxAction = function() {
      if (self.searchCollum() == 'deposit') {
          self.filterDeposits();
      } else {
          self.filterPayments();
      }
  };

  this.filterPayments = function() {
      self.processPayment(true);
      self.deposits([]);
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
              self.payments(response.payments);
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

              $('#'+self.sortColumn()).find('i').show();
              $.each($('#payments-block .sort i'), function( index, value ) {
                  $(this).hide();
              });

              $('.status-text-helper')
                  .tooltip({
                      items: 'span',
                      position: { my: 'left center', at: 'right+30 center' }
                  })
                  .off("mouseover")
                  .on("click", function () {
                      var message = $(this).prev().attr('title');
                      $(this).tooltip("option", "content", self.prepareMessage(message));
                      $(this).tooltip("open");

                      return false;
                  });
          }
      });
  };

  this.filterDeposits = function() {
      self.processPayment(true);
      self.payments([]);
      var filter = $('#depositTypeStatus').linkselect('val');
      if (typeof filter != 'string') {
          filter = '';
      }
      $.ajax({
          url: Routing.generate('landlord_deposits_list'),
          type: 'POST',
          dataType: 'json',
          data: {
              'page' : self.current(),
              'limit' : limit,
              'filter': filter
          },
          success: function(response) {
              self.processPayment(false);
              self.deposits(response.deposits);
              self.total(response.total);
              self.pages(response.pagination);
              $('.status-text-helper')
                  .tooltip({
                      items: 'span',
                      position: { my: 'left center', at: 'right+30 center' }
                  })
                  .off("mouseover")
                  .on("click", function () {
                      var message = $(this).prev().attr('title');
                      $(this).tooltip("option", "content", self.prepareMessage(message));
                      $(this).tooltip("open");

                      return false;
                  });
          }
      });
  };

  this.search = function() {
    if(self.searchCollum() != 'status') {
      if(self.searchText().length <= 0) {
        $('#searsh-field-payments').css('border-color', 'red');
        return;
      } else {
        $('#searsh-field-payments').css('border-color', '#bdbdbd');
      }
    } else {
      var searchText = $('#searchPaymentsStatus').linkselect('val');
      if(typeof searchText != 'string') {
         searchText = '';
      }
      self.searchText(searchText);
    }

    self.current(1);
    self.ajaxAction();
  };

  this.clearSearch = function() {
    self.searchText('');
    self.current(1);
    self.ajaxAction();
  };

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
     if (self.searchCollum() != 'deposit') {
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
     }
  };

  this.togglePayments = function(deposit) {
      if ($('.toggled-' + deposit.batch).first().is(':visible')) {
          $('.toggled-' + deposit.batch).hide();
          $('#title-show-' + deposit.batch).show();
          $('#title-hide-' + deposit.batch).hide();
      } else {
          $('.toggled-' + deposit.batch).show();
          $('#title-show-' + deposit.batch).hide();
          $('#title-hide-' + deposit.batch).show();
      }
  };

  this.toggledZebraCss = function(batchId, index, isRoot) {
      var cssClass =  '';
      if (!isRoot) {
          cssClass += 'toggled-' + batchId;
      }
      if (index%2 == 0) {
          cssClass += ' zebra-tr-dark';
      }

      return cssClass;
  };

  this.depositTitle = function(deposit) {
      var amount = deposit.orders.length;
      return Translator.transChoice('payments.batched_amount', amount, {"count": amount});
  };

  this.getOrderStatusText = function(isDeposit, order) {
      if (isDeposit) {
          return Translator.trans('order.status.text.complete');
      }

      return Translator.trans(order.status);
  };

  this.getOrderAmount = function(isDeposit, order) {
      if (!isDeposit) {
          return '-' + order.amount;
      }

      return order.amount;
  };

  this.isSuccessfulStatus = function(status)
  {
      if (status == 'order.status.text.new' ||
          status == 'order.status.text.complete' ||
          status == 'order.status.text.pending') {
             return true;
      }

      return false;
  };

  this.prepareMessage = function(message)
  {
      if (message) {
          return Translator.trans('order.status.message', {"message" : message});
      }

      return Translator.trans('order.status.message.is_empty');
  };



  this.haveData = ko.computed(function() {
      if (self.deposits().length == 0 && self.payments().length == 0 && !self.processPayment()) {
          return false;
      }

      return true;
  });
}

var PaymentsViewModel = new Payments();

$(document).ready(function(){
    ko.applyBindings(PaymentsViewModel, $('#payments-block').get(0));
    PaymentsViewModel.ajaxAction();
    $('#searchPayments').linkselect("destroy");
    $('#searchPayments').linkselect({
    change: function(li, value, text){
        PaymentsViewModel.current(1);
        PaymentsViewModel.searchText('');
        PaymentsViewModel.searchCollum(value);
    }
    });
});
