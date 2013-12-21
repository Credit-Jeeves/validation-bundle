var BillingAccountViewModel,
    __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

BillingAccountViewModel = (function() {

    function BillingAccountViewModel(data) {
        this.billingAccounts = ko.observableArray([]);
        var mapping = new Mapping();
        ko.mapping.fromJS(data, mapping.billingAccount, this.billingAccounts);

        this.currentBillingAccount = ko.observable(new BillingAccount());

        this.save = __bind(this.save, this);
        this.delete = __bind(this.delete, this);
        this.edit = __bind(this.edit, this);
        this.showDialog = __bind(this.showDialog, this);
        this.closeDialog = __bind(this.closeDialog, this);
        this.showDeleteDialog = __bind(this.showDeleteDialog, this);
        this.closeDeleteDialog = __bind(this.closeDeleteDialog, this);

        jsfv['directDepositType'].addError = window.formProcess.addFormError;
    }

    BillingAccountViewModel.prototype.showDialog = function(account) {
        if (account instanceof BillingAccount) {
            this.currentBillingAccount(account);
        } else {
            this.currentBillingAccount(new BillingAccount())
        }
        $('#billing-account-edit').dialog('open');
    }

    BillingAccountViewModel.prototype.closeDialog = function() {
        $('#billing-account-edit').dialog('close');
    }

    BillingAccountViewModel.prototype.showDeleteDialog = function(account) {
        this.currentBillingAccount(account);
        $('#billing-account-delete').dialog('open');
    }

    BillingAccountViewModel.prototype.closeDeleteDialog = function() {
        $('#billing-account-delete').dialog('close');
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
            data: data,
            error: function(data) {
                window.formProcess.removeAllErrors('#billing-account-edit ');
                $('#billing-account-edit  .error').removeClass('error');
                window.formProcess.applyErrors(JSON.parse(data.responseText));
                $('#billing-account-edit').hideOverlay();

            },
            success: function(data) {
                self.billingAccounts.push(ko.observable(new BillingAccount(data)));
                $('#billing-account-edit').hideOverlay();
                self.closeDialog();
            }
        });
    }

    BillingAccountViewModel.prototype.delete = function(billingAccount) {
        var self = this;
        $('#billing-account-delete').showOverlay();
        $.ajax({
            url: Routing.generate('landlord_billing_delete', {'accountId': billingAccount.id()}),
            type: 'POST',
            timeout: 30000, // 30 secs
            dataType: 'json',
            error: function(data) {
                window.formProcess.removeAllErrors('#billing-account-delete ');
                window.formProcess.addFormError('#billing-account-delete-form', JSON.parse(data.responseText).error);
                $('#billing-account-delete').hideOverlay();

            },
            success: function(data) {
                self.billingAccounts.remove(
                    function(item) {
                        return item().id() == billingAccount.id()
                    }
                );
                $('#billing-account-delete').hideOverlay();
                self.closeDeleteDialog();
            }
        });
    }

    BillingAccountViewModel.prototype.edit = function() {

    }

    return BillingAccountViewModel;

})();
