function accountingImport() {
    var self = this;
    this.rowsTotal = ko.observable(0);
    this.errorLoadDataMessage = ko.observable('');
    this.isProcessing = ko.observable(false);
    this.rows =  ko.observableArray([]);
    this.loadData = function(next) {
        self.isProcessing(true);
        self.rows([]);
        self.rowsTotal(0);
        $.ajax({
            url: Routing.generate('landlord_reports_review_get_rows'),
            type: 'POST',
            dataType: 'json',
            data: {
                'next': next
            },
            success: function(response) {
                self.isProcessing(false);
                self.errorLoadDataMessage(response.message);
                if (response.error === false) {
                    self.rows(response.rows);
                    self.rowsTotal(response.total);
                    self.initGuiScript();
                    return;
                }
            }
        });
    };

    this.getMoveOut = function(data) {
        if (data.moveOut !== undefined) {
            return data.moveOut;
        }

        return '';
    }

    this.initGuiScript = function() {

    };

    this.getStatusText = function(data) {
        if (data.isSkipped) {
            return Translator.trans('import.status.skip');
        }

        if (data.Tenant.contracts[0].status == 'finished') {
            return Translator.trans('import.status.ended');
        }

        if (data.Tenant.contracts[0].id !== undefined && data.Tenant.id !== undefined) {
            return Translator.trans('import.status.match');
        }

        return Translator.trans('import.status.new');
    };

    this.formatDate = function(dateString) {
        if (!dateString || dateString.length <= 0) {
            return '';
        }
        var date = new Date(dateString);
        return (date.getMonth() + 1) + '/' + date.getDate() + '/' +  date.getFullYear();
    }

    //Have problem with implementation datepicker
    this.getUniqueId = function(i)
    {
        var id = "id" + Math.random().toString(16).slice(2);
        return id;
    }

    this.isProcessing.subscribe(function(newValue) {
        if (newValue) {
            $('#reviewContainer').parent().showOverlay();
            return;
        }
        $('#reviewContainer').parent().hideOverlay();
    });

    this.submitForms = function() {
        self.isProcessing(true);
        var number = 0;
        var success = Array();
        var errors = Array();
        $.each($('.line'), function (key,value) {
            var element = $(this);
            //not allow send knockout duplicate
            if (element.find('td').length >= 12) {
                return;
            }
            $.ajax({
                url: Routing.generate('landlord_reports_review_save_row'),
                type: 'POST',
                async: false,
                dataType: 'json',
                data: {
                    'line': self.rows()[number].number
                },
                success: function(response) {
                    var row = self.rows()[number];
                    success.push(row);
                }
            });
            number++;
        });

        $.each(success, function (key,value) {
            self.rows.remove(value);
        });

        self.isProcessing(false);
        if (errors.length <= 0) {
            self.loadData(true);
        } else {
            //process forms errors
            console.info('We need show errors for user');
        }
    }

}
