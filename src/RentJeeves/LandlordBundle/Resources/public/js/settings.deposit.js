
$(document).ready(function(){

    $('#billing-account-edit').dialog({
        autoOpen: false,
        resizable: false,
        modal: true,
        width:'520px'
    });

    $('.add-account').click(function(){
        $('#billing-account-edit').dialog('open');
    });

    function saveBillingAccount() {
        jQuery('#billing-account-edit').showOverlay();

        var data = jQuery('#rentjeeves_landlordbundle_bankaccounttype').serializeArray();

        jQuery.ajax({
            url: Routing.generate('landlord_billing_save'),
            type: 'POST',
            timeout: 30000, // 30 secs
            dataType: 'json',
            data: jQuery.param(data, false),
            error: function(jqXHR, textStatus, errorThrown) {
                window.formProcess.removeAllErrors();
                jQuery('#payment-account-edit').hideOverlay();
                window.formProcess.reLogin(jqXHR, errorThrown);
                window.formProcess.addFormError(null, errorThrown);
            },
            success: function(data, textStatus, jqXHR) {
                window.formProcess.removeAllErrors('#payment-account-edit ');

                jQuery('#payment-account-edit').hideOverlay();
                if (!data.success) {
                    window.formProcess.applyErrors(data);
                    return;
                }

                jQuery('body').showOverlay();
                window.location.reload();
            }
        });

        return false;
    }
});
