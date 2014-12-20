function accountingImportCsv() {

    var self = this;
    this.source = ko.observable('csv');
    this.classLoadDataMessage = ko.observable('');
    this.importOnlyException = ko.observable(importOnlyException);

    this.doFinish = function()
    {
        self.loadDataMessage('');
        self.importOnlyException(false);
        self.classLoadDataMessage('');
        self.showSpinner(false);

        return self.superclass.loadData(false);
    }

    this.showError = function()
    {
        self.loadDataMessage(Translator.trans('csv.import.error.update_matched_contracts'));
        self.classLoadDataMessage('errorMessage');
        self.showSpinner(false);
    }

    this.loadData = function(next) {
        if (self.importOnlyException() === true) {
            self.showSpinner(true);
            self.loadDataMessage(Translator.trans('csv.import.message.updated_matched_contract'));
            jQuery.ajax({
                url: Routing.generate('updateMatchedContractsCsv'),
                type: 'POST',
                dataType: 'json',
                error: function() {
                    self.showError();
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
