function accountingImportYardi() {

    var self = this;
    this.source = ko.observable('yardi');
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

    this.saveContractData = function (residentsId, i, length) {
        if (length > i) {
            self.loadDataMessage(
                Translator.trans('yardi.import.message.download.contracts', {"RESIDENT_ID":residentsId[i].tCode})
            );
            jQuery.ajax({
                url: Routing.generate('accounting_import_resident_data_yardi',
                    {
                        'residentId': residentsId[i].tCode,
                        'isLast': (length-1 === i)? 1 : 0
                    }
                ),
                type: 'POST',
                data: {
                    'resident':residentsId[i]
                },
                dataType: 'json',
                error: function () {
                    //@TODO show some info for user with failed resident
                    i++;
                    self.saveContractData(residentsId, i, length);
                },
                success: function (response) {
                    i++;
                    self.saveContractData(residentsId, i, length);
                }
            });
        } else {
            return self.doFinish();
        }
    }


    this.loadData = function(next) {
        if (self.isFinishUploadDataToServer() === false) {
            self.showSpinner(true);
            self.loadDataMessage(Translator.trans('yardi.import.message.download.resident'));
            jQuery.ajax({
                url: Routing.generate('accounting_import_residents_yardi'),
                type: 'POST',
                dataType: 'json',
                error: function() {
                    self.loadDataMessage(Translator.trans('yardi.import.error.getResidents'));
                    self.classLoadDataMessage('errorMessage');
                    self.showSpinner(false);
                },
                success: function(response) {
                    var length = 0;
                    $.each(response, function( key, value ) {
                        length++;
                    });

                    if (length > 0) {
                        self.saveContractData(response, 0, length);
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

