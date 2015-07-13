function accountingImportYardi() {

    var self = this;
    this.source = ko.observable('yardi');
    this.isFinishUploadDataToServer =  ko.observable(false);
    this.classLoadDataMessage = ko.observable('');

    this.currentPropertyMappingId = null;
    this.loadedResidents = [];
    this.loadedMappedProperty = [];

    this.doFinish = function()
    {
        self.loadDataMessage('');
        self.isFinishUploadDataToServer(true);
        self.classLoadDataMessage('');
        self.showSpinner(false);

        return self.superclass.loadData(false);
    };

    this.saveContractData = function (step, countResidents, i, length) {
        if (length > i) {
            self.loadDataMessage(
                Translator.trans(
                    'yardi.import.message.download.contracts',
                    {"RESIDENT_ID": self.loadedResidents[i].tCode}
                )
            );
            jQuery.ajax({
                url: Routing.generate('accounting_import_resident_data_yardi',
                    {
                        'propertyMappingId': self.currentPropertyMappingId,
                        'isLast': (countResidents - 1 === step) && (length - 1 === i) ? 1 : 0
                    }
                ),
                type: 'POST',
                data: {
                    'resident': self.loadedResidents[i]
                },
                dataType: 'json',
                error: function () {
                    //@TODO show some info for user with failed resident
                    i++;
                    self.saveContractData(step, countResidents, i, length);
                },
                success: function (response) {
                    i++;
                    self.saveContractData(step, countResidents, i, length);
                }
            });
        } else {
            step++;
            self.getResidents(step, countResidents);
        }
    };

    this.getResidents = function (i, length) {
        if (length > i) {
            self.currentPropertyMappingId = self.loadedMappedProperty[i].id;
            self.loadDataMessage(
                Translator.trans(
                    'yardi.import.message.download.residents',
                    {"EXTERNAL_PROPERTY_ID":self.loadedMappedProperty[i].external_property_id}
                )
            );

            jQuery.ajax({
                url: Routing.generate('accounting_import_residents_yardi',
                    {
                        'propertyMappingId': this.currentPropertyMappingId
                    }
                ),
                type: 'GET',
                dataType: 'json',
                error: function() {
                    i++;
                    self.getResidents(i, length);
                },
                success: function(response) {
                    var countResidents = 0;
                    $.each(response, function () { countResidents++ });

                    if (countResidents > 0) {
                        self.loadedResidents = response;
                        self.saveContractData(i, length, 0, countResidents);
                    } else {
                        i++;
                        self.getResidents(i, length);
                    }
                }
            });
        } else {
            self.doFinish();
        }
    };

    this.loadData = function(next) {
        if (self.isFinishUploadDataToServer() === false) {
            self.showSpinner(true);
            self.loadDataMessage(Translator.trans('yardi.import.message.download.property_mapping'));
            jQuery.ajax({
                url: Routing.generate('accounting_import_property_mapping_yardi'),
                type: 'POST',
                dataType: 'json',
                error: function() {
                    self.loadDataMessage(Translator.trans('yardi.import.error.getPropertyMapping'));
                    self.classLoadDataMessage('errorMessage');
                    self.showSpinner(false);
                },
                success: function(response) {
                    var countProperties = 0;
                    $.each(response, function () { countProperties++ });

                    if (countProperties > 0) {
                        self.loadedMappedProperty = response;
                        self.getResidents(0, countProperties);
                    } else {
                        self.doFinish();
                    }
                }
            });
        } else {
            return self.doFinish();
        }
    }
}

