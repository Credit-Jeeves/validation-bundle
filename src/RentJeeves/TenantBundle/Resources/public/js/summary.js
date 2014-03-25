$(document).ready(function(){
    $(function() {
      $("#pricing-popup").dialog({
        width:660,
        autoOpen: false,
        modal:true
      });
    });

    $('.show-scoretrack-pricing-popup').click(function(){
      $("#pricing-popup").dialog('open');
    });
    $('#pricing-popup button.button-close').click(function(){
      $("#pricing-popup").dialog('close');
    });
});
