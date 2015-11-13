function accountingImportResman() {

    var self = this;
    this.source = ko.observable('resman');
    this.isFinishUploadDataToServer =  ko.observable(false);
    this.classLoadDataMessage = ko.observable('');

    this.currentProperty = null;
    this.loadedResidents = [];
    this.externalPropertyIds = [];

    this.doFinish = function()
    {
        self.loadDataMessage('');
        self.isFinishUploadDataToServer(true);
        self.classLoadDataMessage('');
        self.showSpinner(false);

        return self.superclass.loadData(false);
    };

    this.saveContractData = function (i, length) {
        if (length > i) {
            self.currentProperty = self.externalPropertyIds[i];
            self.loadDataMessage(
                Translator.trans(
                    'import.message.download.contracts',
                    {"EXTERNAL_PROPERTY_ID": self.currentProperty}
                )
            );
            console.info('Save contracts for external property id ' + self.currentProperty);
            jQuery.ajax({
                url: Routing.generate('accounting_import_residents_resman',
                    {
                        'externalPropertyId': self.currentProperty
                    }
                ),
                type: 'POST',
                dataType: 'json',
                error: function() {
                    console.error('Error when try to save contracts for external property id ' + self.currentProperty);
                    i++;
                    self.saveContractData(i, length);
                },
                success: function(response) {
                    i++;
                    self.saveContractData(i, length);
                }
            });
        } else {
            self.doFinish();
        }
    };

    this.loadData = function(next) {
        if (self.isFinishUploadDataToServer() === false) {
            self.showSpinner(true);
            self.loadDataMessage(Translator.trans('resman.import.message.load.external_property_ids'));
            console.info('Get External Property List');
            jQuery.ajax({
                url: Routing.generate('accounting_import_load_resman_external_property_ids'),
                type: 'GET',
                dataType: 'json',
                error: function() {
                    self.loadDataMessage(Translator.trans('resman.import.error.loadExternalPropertyIds'));
                    self.classLoadDataMessage('errorMessage');
                    self.showSpinner(false);
                },
                success: function(externalPropertyIds) {
                    var countProperties = 0;
                    $.each(externalPropertyIds, function () { countProperties++ });

                    if (countProperties > 0) {
                        self.externalPropertyIds = externalPropertyIds;
                        self.saveContractData(0, countProperties);
                    } else {
                        self.doFinish();
                    }
                }
            });
        } else {
            return self.doFinish();
        }
    };
}
