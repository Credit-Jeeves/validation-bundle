var BillingAccountViewModel,
    __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

BillingAccountViewModel = (function() {

    function BillingAccountViewModel(data) {
        this.billingAccounts = ko.observableArray([]);
        var mapping = new Mapping();
        ko.mapping.fromJS(data, mapping.billingAccount, this.billingAccounts);

        this.newBillingAccount = ko.observable(new BillingAccount());

        this.save = __bind(this.save, this);
        this.showDialog = __bind(this.showDialog, this);
        this.closeDialog = __bind(this.closeDialog, this);
    }

    BillingAccountViewModel.prototype.showDialog = function() {
        $('#billing-account-edit').dialog('open');
    }

    BillingAccountViewModel.prototype.closeDialog = function() {
        $('#billing-account-edit').dialog('close');
    }

    BillingAccountViewModel.prototype.save = function() {
        var self = this;
        $('#billing-account-edit').showOverlay();

        var data = $('#directDepositType').serializeArray();

        $.ajax({
            url: Routing.generate('landlord_billing_save'),
            type: 'POST',
            timeout: 30000, // 30 secs
            dataType: 'json',
            data: ko.mapping.toJSON(this.newBillingAccount()),
            error: function(jqXHR, textStatus, errorThrown) {
                $('#payment-account-edit').hideOverlay();
            },
            success: function(data, textStatus, jqXHR) {
                var acc = self.newBillingAccount();
                self.billingAccounts.push(ko.observable(acc));
                self.newBillingAccount(new BillingAccount());
                $('#billing-account-edit').hideOverlay();
                self.closeDialog();

//                if (!data.success) {
//                    window.formProcess.applyErrors(data);
//                    return;
//                }

//                $('body').showOverlay();
            }
        });

        return false;
    }

    return BillingAccountViewModel;

})();
