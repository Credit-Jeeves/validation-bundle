function Payments() {
  var limit = 10;
  var current = 1;
  var self = this;
  this.payments = ko.observableArray([]);
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
  this.showCashPayments = ko.observable(false);

  this.showCashPayments.subscribe(function(newValue) {
      self.ajaxAction();
  });

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
                  'searchText': self.searchText(),
                  'showCashPayments': self.showCashPayments()
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

  this.orderStatusTitle = function(order) {
      if (this.isSuccessfulStatus(order.status)) {
          return Translator.trans('landlord_dashboard.payment.title', {"created": order.paymentCreated, "charged": order.start});
      }

      return order.errorMessage;
  };

  this.getOrderStatusText = function(isDeposit, order) {
      if (isDeposit) {
          return Translator.trans('order.status.text.complete');
      }

      return Translator.trans(order.status);
  };

  this.getTenantWithDepositTitle = function(order) {
      var message = '';
      if (order.depositType.length == 0 || order.depositType.toLowerCase() == 'rent') {
          message += order.tenant;
      } else {
          message += Translator.trans(
              'landlord.dashboard.additional_paid_for',
              {
                  'paid_for': Translator.trans(order.depositType),
                  'tenant': order.tenant
              }
          );
      }
      if(order.checkNumber) {
          message += ' | ' + Translator.trans('payment.order.check_number.title', {checkNumber: order.checkNumber})
      }
      return message;
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
          status == 'order.status.text.pending' ||
          status == 'order.status.text.sending') {
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
      if (self.payments().length == 0 && !self.processPayment()) {
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
            if (value === 'batch_deposit_report') {
                $('#payments-block').showOverlay();
                window.location = Routing.generate('accounting_deposit');
            } else {
                PaymentsViewModel.current(1);
                PaymentsViewModel.searchText('');
                PaymentsViewModel.searchCollum(value);
            }
        }
    });
});
