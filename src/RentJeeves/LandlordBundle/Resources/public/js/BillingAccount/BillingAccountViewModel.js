var BillingAccountViewModel,
    __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

BillingAccountViewModel = (function() {

    function BillingAccountViewModel(data) {
        this.billingAccounts = ko.observableArray([]);
        var mapping = new Mapping();
        ko.mapping.fromJS(data, mapping.billingAccount, this.billingAccounts);

        this.currentBillingAccount = ko.observable(new BillingAccount());
        this.isCreateMode = ko.observable(false);
        this.isLoading = ko.observable(false);

        this.create = function() {
            var self = this;
            var data = $('#directDepositType').serializeArray();

            $.ajax({
                url: Routing.generate('landlord_billing_create'),
                type: 'POST',
                timeout: 60000,
                dataType: 'json',
                data: data,
                error: function(data) {
                    window.formProcess.removeAllErrors('#billing-account-edit ');
                    $('#billing-account-edit  .error').removeClass('error');
                    window.formProcess.applyErrors(JSON.parse(data.responseText));
                    $('#billing-account-edit').hideOverlay();

                },
                success: function(data) {
                    self.refresh();
                    $('#billing-account-edit').hideOverlay();
                    self.closeDialog();
                }
            });
        }

        this.edit = function() {
            var self = this;
            var data = $('#directDepositType').serializeArray();

            $.ajax({
                url: Routing.generate('landlord_billing_edit'),
                type: 'POST',
                timeout: 30000,
                dataType: 'json',
                data: data,
                error: function(data) {
                    window.formProcess.removeAllErrors('#billing-account-edit ');
                    $('#billing-account-edit  .error').removeClass('error');
                    window.formProcess.applyErrors(JSON.parse(data.responseText));
                    $('#billing-account-edit').hideOverlay();

                },
                success: function(data) {
                    self.refresh();
                    $('#billing-account-edit').hideOverlay();
                    self.closeDialog();
                }
            });
        }

        this.refresh = function() {
//            $('#payment-accounts-list').showOverlay();
            this.isLoading(true);
            var self = this;

            $.ajax({
                url: Routing.generate('landlord_billing_refresh'),
                type: 'GET',
                timeout: 30000,
                dataType: 'json',
                success: function(data) {
                    ko.mapping.fromJS(data, mapping.billingAccount, self.billingAccounts);
//                    $('#payment-accounts-list').hideOverlay();
                    self.isLoading(false);
                }
            });
        }

        this.save = __bind(this.save, this);
        this.delete = __bind(this.delete, this);
        this.showEditPopup = __bind(this.showEditPopup, this);
        this.showCreatePopup = __bind(this.showCreatePopup, this);
        this.showDeletePopup = __bind(this.showDeletePopup, this);
        this.showDialog = __bind(this.showDialog, this);
        this.closeDialog = __bind(this.closeDialog, this);
        this.showDeleteDialog = __bind(this.showDeleteDialog, this);
        this.closeDeleteDialog = __bind(this.closeDeleteDialog, this);

        jsfv['directDepositType'].addError = window.formProcess.addFormError;
    }

    BillingAccountViewModel.prototype.showEditPopup = function(account) {
        this.currentBillingAccount(account);
        this.isCreateMode(false);
        this.showDialog();
    }

    BillingAccountViewModel.prototype.showCreatePopup = function() {
        this.currentBillingAccount(new BillingAccount());
        this.isCreateMode(true);
        this.showDialog();
    }

    BillingAccountViewModel.prototype.showDeletePopup = function(account) {
        this.currentBillingAccount(account);
        this.showDeleteDialog();
    }

    BillingAccountViewModel.prototype.showDialog = function() {
        $('#billing-account-edit').dialog('open');
    }

    BillingAccountViewModel.prototype.closeDialog = function() {
        $('#billing-account-edit').dialog('close');
    }

    BillingAccountViewModel.prototype.showDeleteDialog = function() {
        $('#billing-account-delete').dialog('open');
    }

    BillingAccountViewModel.prototype.closeDeleteDialog = function() {
        $('#billing-account-delete').dialog('close');
    }

    BillingAccountViewModel.prototype.save = function() {
        $('#billing-account-edit').showOverlay();

        if (this.isCreateMode()) {
            this.create();
        } else {
            this.edit();
        }
    }

    BillingAccountViewModel.prototype.delete = function(billingAccount) {
        var self = this;
        $('#billing-account-delete').showOverlay();
        $.ajax({
            url: Routing.generate('landlord_billing_delete', {'accountId': billingAccount.id()}),
            type: 'POST',
            timeout: 30000,
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
