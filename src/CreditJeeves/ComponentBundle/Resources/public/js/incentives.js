function Incentives() {
  
//  ko.bindingHandlers.i18n = {
//      init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
//        console.log($(element).html());
//        console.log( valueAccessor());
//          // This will be called when the binding is first applied to an element
//          // Set up any initial state, event handlers, etc. here
//      },
//      update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
//        console.log('-----');
//          // This will be called once when the binding is first applied to an element,
//          // and again whenever the associated observable changes value.
//          // Update the DOM element based on the supplied values here.
//      }
//  };  
  
  var self = this;
  this.aNegativeTradelines = ko.observableArray();
  this.aIncentiveTradelines = ko.observableArray();
  this.sUrl = '';
  this.ajaxAction = function(sAction, nTradelineId) {
    $.ajax({
      url: this.sUrl,
      type: 'POST',
      dataType: 'json',
      data: {
              'do_action': sAction,
              'tradeline': nTradelineId,
            },
      success: function(response) {
        if (response.id != nTradelineId) {
          return true;
        }
        if (sAction == 'completed') {
          self.moveAction(nTradelineId, response.incentive);
        } else {
          self.changeAction(nTradelineId, sAction);
        }
      }
    });
  };
  this.changeAction = function(nTradelineId, sAction) {
    $('#fixed-' + nTradelineId).remove();
    var dParent = $('#disputed-' + nTradelineId).parent();
    
    var dCompleted = $('#disputed-' + nTradelineId).clone(false);
    dCompleted.removeClass('disputed')
      .addClass('completed')
      .attr('id', 'completed-' + nTradelineId)
      .html('Completed');
    dParent.empty();
    dParent.append(dCompleted);
    for (var i = 0; i < self.aNegativeTradelines().length; i++) {
      if (self.aNegativeTradelines()[i].id == nTradelineId) {
        var aTradeline = self.aNegativeTradelines()[i];
        switch (sAction) {
          case 'fixed':
            aTradeline['is_fixed'] = true;
            break;
          case 'disputed':
            aTradeline['is_disputed'] = true;
            break;
          case 'rollback':
            aTradeline['is_fixed'] = false;
            aTradeline['is_disputed'] = false;
            break;
        }
        var aTradelines = self.aNegativeTradelines();
        self.aNegativeTradelines([]);
        self.aNegativeTradelines(aTradelines);
        $('.action-steps ul li:odd').removeClass('even-stripe').addClass('odd-stripe');
        $('.action-steps ul li:even').removeClass('odd-stripe').addClass('even-stripe');
      }
    }
    $('#action-steps').delegate('.fixed', 'click',function(){
      var nTradelineId = this.id.split('-')[1];
      self.ajaxAction('fixed', nTradelineId);
      return false;
    });
    $('#action-steps').delegate('.disputed', 'click',function(){
      var nTradelineId = this.id.split('-')[1];
      self.ajaxAction('disputed', nTradelineId);
      return false;
    });
    $('#action-steps').delegate('.completed', 'click', function(){
      var nTradelineId = this.id.split('-')[1];
      self.ajaxAction('completed', nTradelineId);
      return false;
    });
    $('#action-steps').delegate('.rollback', 'click', function(){
      var nTradelineId = this.id.split('-')[2];
      self.ajaxAction('rollback', nTradelineId);
      return false;
    });
    $('.lightbox-anywhere').click(function(event){
      event.preventDefault();
      var sLink = $(this).attr('href') || 'http://creditjeeves.uservoice.com/knowledgebase';
      if (sLink == '#') {
        sLink = 'http://creditjeeves.uservoice.com/knowledgebase';
      }
      LightBox.show(sLink);
    });
  };
  this.moveAction = function(nTradelineId, sIncentive) {
    for (var i = 0; i < self.aNegativeTradelines().length; i++) {
      if (self.aNegativeTradelines()[i].id == nTradelineId) {
        var aTradeline = self.aNegativeTradelines()[i];
        aTradeline['incentive'] = sIncentive;
        self.aNegativeTradelines.splice(i, 1);
        self.aIncentiveTradelines.push(aTradeline);
      }
    }
    $('.action-steps ul li:odd').removeClass('even-stripe').addClass('odd-stripe');
    $('.action-steps ul li:even').removeClass('odd-stripe').addClass('even-stripe');
    $('.lightbox-anywhere').click(function(event){
      event.preventDefault();
      var sLink = $(this).attr('href') || 'http://creditjeeves.uservoice.com/knowledgebase';
      if (sLink == '#') {
        sLink = 'http://creditjeeves.uservoice.com/knowledgebase';
      }
      LightBox.show(sLink);
    });
    $('#action-steps').delegate('.fixed', 'click',function(){
      var nTradelineId = this.id.split('-')[1];
      IncentiveModel.ajaxAction('fixed', nTradelineId);
      return false;
    });
    $('#action-steps').delegate('.disputed', 'click',function(){
      var nTradelineId = this.id.split('-')[1];
      IncentiveModel.ajaxAction('disputed', nTradelineId);
      return false;
    });
    $('#action-steps').delegate('.completed', 'click', function(){
      var nTradelineId = this.id.split('-')[1];
      IncentiveModel.ajaxAction('completed', nTradelineId);
      return false;
    });
    $('#action-steps').delegate('.rollback', 'click', function(){
      var nTradelineId = this.id.split('-')[2];
      IncentiveModel.ajaxAction('rollback', nTradelineId);
      return false;
    });
    $('#action-steps').delegate('.lightbox-anywhere', 'click', function(event){
      event.preventDefault();
      var sLink = $(this).attr('href') || 'http://creditjeeves.uservoice.com/knowledgebase';
      if (sLink == '#') {
        sLink = 'http://creditjeeves.uservoice.com/knowledgebase';
      }
      LightBox.show(sLink);
    });    
  };
  this.countTradelines = ko.computed(function(){
    return parseInt(self.aNegativeTradelines().length) + parseInt(self.aIncentiveTradelines().length);
  });
}
$(document).ready(function(){
  var aNegative = IncentiveModel.aNegativeTradelines();
  IncentiveModel.aNegativeTradelines([]);
  IncentiveModel.aNegativeTradelines(aNegative);
  $('#action-steps').delegate('.fixed', 'click',function(){
    var nTradelineId = this.id.split('-')[1];
    IncentiveModel.ajaxAction('fixed', nTradelineId);
    return false;
  });
  $('#action-steps').delegate('.disputed', 'click',function(){
    var nTradelineId = this.id.split('-')[1];
    IncentiveModel.ajaxAction('disputed', nTradelineId);
    return false;
  });
  $('#action-steps').delegate('.completed', 'click', function(){
    var nTradelineId = this.id.split('-')[1];
    IncentiveModel.ajaxAction('completed', nTradelineId);
    return false;
  });
  $('#action-steps').delegate('.rollback', 'click', function(){
    var nTradelineId = this.id.split('-')[2];
    IncentiveModel.ajaxAction('rollback', nTradelineId);
    return false;
  });
  $('#action-steps').delegate('.lightbox-anywhere', 'click', function(event){
    event.preventDefault();
    var sLink = $(this).attr('href') || 'http://creditjeeves.uservoice.com/knowledgebase';
    if (sLink == '#') {
      sLink = 'http://creditjeeves.uservoice.com/knowledgebase';
    }
    LightBox.show(sLink);
  });
  
});
