function BillingAccountViewModel(data)
{
    var self = this;

    this.showEditPopup = function(account)
    {
        self.currentBillingAccount(account);
        self.isCreateMode(false);
        self.showDialog();
    };

    this.showCreatePopup = function()
    {
        self.currentBillingAccount(new BillingAccount());
        self.isCreateMode(true);
        self.showDialog();
    };

    this.showDeletePopup = function(account)
    {
        self.currentBillingAccount(account);
        self.showDeleteDialog();
    };

    this.showDialog = function()
    {
        $('#billing-account-edit').dialog('open');
    };

    this.closeDialog = function()
    {
        $('#billing-account-edit').dialog('close');
    };

    this.showDeleteDialog = function()
    {
        $('#billing-account-delete').dialog('open');
    };

    this.closeDeleteDialog = function()
    {
        $('#billing-account-delete').dialog('close');
    };

    this.save = function()
    {
        $('#billing-account-edit').showOverlay();

        if (self.isCreateMode()) {
            self.create();
        } else {
            self.edit();
        }
    };

    this.delete = function(billingAccount)
    {
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
    };

    this.billingAccounts = ko.observableArray([]);
    var mapping = new Mapping();
    ko.mapping.fromJS(data, mapping.billingAccount, this.billingAccounts);

    this.currentBillingAccount = ko.observable(new BillingAccount());
    this.isCreateMode = ko.observable(false);
    this.isLoading = ko.observable(false);

    this.create = function() {
        var data = $('#billingAccountType').serializeArray();

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
    };

    this.edit = function() {
        var data = $('#billingAccountType').serializeArray();

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
    };

    this.refresh = function() {
        this.isLoading(true);

        $.ajax({
            url: Routing.generate('landlord_billing_refresh'),
            type: 'GET',
            timeout: 30000,
            dataType: 'json',
            success: function(data) {
                ko.mapping.fromJS(data, mapping.billingAccount, self.billingAccounts);
                self.isLoading(false);
            }
        });
    };
}
