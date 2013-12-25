$(document).ready(function(){

    $('#billing-account-edit').dialog({
        autoOpen: false,
        resizable: false,
        modal: true,
        width:'520px'
    });

    $('#billing-account-delete').dialog({
        autoOpen: false,
        resizable: false,
        modal: true,
        width:'520px'
    });

    jsfv['billingAccountType'].addError = window.formProcess.addFormError;
});
