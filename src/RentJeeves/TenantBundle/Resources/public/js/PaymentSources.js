function PaymentSources() {
    var self = this;
    var formName = 'rentjeeves_checkoutbundle_paymentaccounttype';

    // Connected Payment Source Component
    ko.utils.extend(self, new PaymentSourceViewModel(self));

    ko.utils.extend(self, new PayAddress(self));

    self.changePaymentAccountHandler = function() {
        self.billingaddress.addressChoice(self.currentPaymentAccount().addressId());
    };

    this.delUrl = ko.computed(function() {
        return Routing.generate('tenant_payment_sources_del', { id: self.currentPaymentAccountId() });
    });
    this.edit = function(id) {
        self.currentPaymentAccountId(id);
        window.formProcess.removeAllErrors('#payment-account-edit ');
        $("#payment-account-edit").dialog({
            width:650,
            modal:true
        });
    };
    this.editSave = function() {
        jQuery('#payment-account-edit').showOverlay();

        var data = jQuery('#' + formName).serializeArray();

        jQuery.ajax({
            url: Routing.generate('tenant_payment_sources_save'),
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
            success: function(data) {
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

    };
    this.editClose = function() {
        $("#payment-account-edit").dialog('close');
    };
    this.delDialog = function(id) {
        self.currentPaymentAccountId(id);
        $("#payment-account-delete").dialog({
            width:400,
            modal:true
        });
    };
    this.delClose = function() {
        $("#payment-account-delete").dialog('close');
    };

    jsfv[formName].addError = window.formProcess.addFormError;
    jsfv[formName].removeErrors = function(field) {};
    jQuery('#' + formName).submit(function() {
        self.editSave();
        return false;
    });

    window.test = self;
}
