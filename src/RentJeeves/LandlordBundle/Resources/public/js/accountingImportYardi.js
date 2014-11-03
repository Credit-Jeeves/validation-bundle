function accountingImportYardi() {
    var self = this;

    this.source = ko.observable('yardi');
    this.isFinishUploadDataToServer =  ko.observable(false);
    this.classLoadDataMessage = ko.observable('');

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
                    'moveOutDate': residentsId[i].MoveOutDate
                },
                dataType: 'json',
                error: function () {
                    self.setProcessing(false);
                    self.downloadImage(false);
                    self.loadDataMessage(Translator.trans('yardi.import.error.getResidents'));
                    self.classLoadDataMessage('errorMessage');
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

    this.doFinish = function()
    {
        self.loadDataMessage('');
        self.isFinishUploadDataToServer(true);
        self.classLoadDataMessage('');
        self.downloadImage(false);
        return self.superclass.loadData(false);
    }

    this.loadData = function(next) {
        if (self.isFinishUploadDataToServer() === false) {
            self.downloadImage(true);
            self.loadDataMessage(Translator.trans('yardi.import.message.download.resident'));
            jQuery.ajax({
                url: Routing.generate('accounting_import_residents_yardi'),
                type: 'POST',
                dataType: 'json',
                error: function() {
                    self.loadDataMessage(Translator.trans('yardi.import.error.getResidents'));
                    self.classLoadDataMessage('errorMessage');
                    self.downloadImage(false);
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

