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

    this.loadData = function(next, nextPageLink) {
        var nextPageLink = typeof nextPageLink !== 'undefined' ? nextPageLink : '';
        if (self.isFinishUploadDataToServer() === false) {
            self.showSpinner(true);
            self.loadDataMessage(Translator.trans('import.message.download.contracts'));
            jQuery.ajax({
                url: Routing.generate('accounting_import_residents_mri'),
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
                        return self.doFinish();
                    }

                    return self.loadData(next, response.nextPageLink);
                }
            });
        } else {
            return self.doFinish();
        }
    }
}

