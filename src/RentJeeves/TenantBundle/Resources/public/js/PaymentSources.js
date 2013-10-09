function PaymentSources() {
    var self = this;
    var formName = 'rentjeeves_checkoutbundle_paymentaccounttype';
    this.newUserAddress = ko.observableArray([]);

    this.paymentSource = new PaymentSource(this, true, null);

    this.delUrl = ko.computed(function() {
        return Routing.generate('tenant_payment_sources_del', { id: self.paymentSource.id() });
    });

    var fillPaymentSource = function(id) {
        jQuery.each(window.paymentAccounts, function(key, val) {
            if (val.id == id) {
                self.paymentSource.clear();
                ko.mapping.fromJS(val, {}, self.paymentSource);
                self.paymentSource.address.addressChoice(val.addressId);
                if (exp = val.cc_expiration) {
                    var date = new Date(exp);
                    self.paymentSource.ExpirationMonth(date.getMonth());
                    self.paymentSource.ExpirationYear(date.getFullYear());
                } else {
                    self.paymentSource.ExpirationMonth(null);
                    self.paymentSource.ExpirationYear(null);
                }
                return false;
            }
            return true;
        });
    };

    this.edit = function(id) {
        fillPaymentSource(id);
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
//            complete: function(jqXHR, textStatus) {
//                jQuery('#pay-popup').hideOverlay();
//            },
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

    };
    this.editClose = function() {
        $("#payment-account-edit").dialog('close');
    };
    this.delDialog = function(id) {
        fillPaymentSource(id);
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
}
