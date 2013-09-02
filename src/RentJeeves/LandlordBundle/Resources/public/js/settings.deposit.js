
$(document).ready(function(){

    $('#deposit-landlord-popup').dialog({ 
        autoOpen: false,
        resizable: false,
        modal: true,
        width:'520px'
    });

    $('.add-account').click(function(){
        $('#deposit-landlord-popup').dialog('open');
    });
});