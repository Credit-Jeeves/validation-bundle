$(document).ready(function(){
    $(function() {
        $("#pricing-popup").dialog({
            width:660,
            autoOpen: false,
            modal:true
        });
    });
    $('#popup-pricing').click(function(){
        $("#pricing-popup").dialog('open');
    });
    $('#pricing-popup button.button-close').click(function(){
        $("#pricing-popup").dialog('close');
    });
});