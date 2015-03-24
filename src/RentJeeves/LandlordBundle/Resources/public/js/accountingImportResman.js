function accountingImportResman() {

    var self = this;
    this.source = ko.observable('resman');
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

    this.loadData = function(next) {
        if (self.isFinishUploadDataToServer() === false) {
            self.showSpinner(true);
            self.loadDataMessage(Translator.trans('import.message.download.contracts'));
            jQuery.ajax({
                url: Routing.generate('accounting_import_residents_resman'),
                type: 'POST',
                dataType: 'json',
                error: function() {
                    self.loadDataMessage(Translator.trans('resman.import.error.getResidents'));
                    self.classLoadDataMessage('errorMessage');
                    self.showSpinner(false);
                },
                success: function(response) {
                    self.doFinish();
                }
            });
        } else {
            return self.doFinish();
        }
    }
}

