function accountingImportMri() {

    var self = this;
    this.source = ko.observable('mri');
    this.isFinishUploadDataToServer =  ko.observable(false);
    this.classLoadDataMessage = ko.observable('');

    this.doFinish = function()
    {
        self.loadDataMessage('');
        self.isFinishUploadDataToServer(true);
        self.classLoadDataMessage('');
        self.showSpinner(false);

        return self.superclass.loadData(false);
    }

    this.loadContracts = function(items, i, nextPageLink)
    {
        if (i >= items.length) {
            return self.doFinish();
        }

        var nextPageLink = typeof nextPageLink !== 'undefined' ? nextPageLink : '';
        self.loadDataMessage(
            Translator.trans(
                'import.message.download.contracts',
                {"EXTERNAL_PROPERTY_ID": items[i]}
            )
        );
        jQuery.ajax({
            url: Routing.generate('accounting_import_residents_mri', {'externalPropertyId': items[i] }),
            type: 'POST',
            dataType: 'json',
            data: {
                'nextPageLink': nextPageLink
            },
            error: function() {
                self.loadDataMessage(Translator.trans('mri.import.error.getResidents'));
                self.classLoadDataMessage('errorMessage');
                self.showSpinner(false);
            },
            success: function(response) {
                if (response.nextPageLink === null) {
                    i++;
                    return self.loadContracts(items, i);
                }

                return self.loadContracts(items, i, response.nextPageLink);
            }
        });
    }

    this.loadData = function() {
        if (self.isFinishUploadDataToServer() === false) {
            self.showSpinner(true);
            self.loadDataMessage(Translator.trans('mri.import.message.download.external_property_id'));
            jQuery.ajax({
                url: Routing.generate('accounting_external_property_ids_mri'),
                type: 'POST',
                dataType: 'json',
                error: function () {
                    self.loadDataMessage(Translator.trans('mri.import.error.getExternalPropertyIds'));
                    self.classLoadDataMessage('errorMessage');
                    self.showSpinner(false);
                },
                success: function (items) {
                    if (items.length === 0) {
                        self.loadDataMessage(Translator.trans('mri.import.error.empty.externalPropertyIds'));
                        self.classLoadDataMessage('errorMessage');
                        self.showSpinner(false);
                        return;
                    }

                    self.loadContracts(items, 0);
                }
            });
        } else {
            self.doFinish();
        }
    }
}

