function Reporting() {
    var self = this;
    this.process = ko.observable(false);
  this.startReporting = function() {
    $.ajax({
      url: Routing.generate('tenant_reporting_start'),
      type: 'POST',
      dataType: 'json',
      data: {
        'action': 'start'
      },
      success: function(response) {
        self.closePopup();
      }
    });
  };
  this.closePopup = function() {
    self.process(false);
  };
}

function Info() {
  this.popup = ko.observable(false);
  this.openPopup = function() {
    ReportingViewModel.process(true);
  };
}

var ReportingViewModel = new Reporting();
var InfoViewModel = new Info();

$(document).ready(function(){
  ko.applyBindings(ReportingViewModel, $('#reporting-popup').get(0));
  ko.applyBindings(InfoViewModel, $('#info-block').get(0));
});