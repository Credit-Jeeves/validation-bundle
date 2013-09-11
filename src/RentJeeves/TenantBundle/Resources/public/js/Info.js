function Info() {
    this.popup = ko.observable(false);
    this.openPopup = function() {
      $('#reporting-popup').dialog('open');
    };
}

var InfoViewModel = new Info();

$(document).ready(function(){
    ko.applyBindings(InfoViewModel, $('#info-block').get(0));
    $('#reporting-popup').dialog({ 
      autoOpen: false,
      modal: true,
      width:'520px'
  });

});
