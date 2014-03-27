function accountingImport() {
    var self = this;
    this.rowsTotal = ko.observable(0);
    this.errorLoadDataMessage = ko.observable('');
    this.isFinishReview =  ko.observable(false);
    this.rows = ko.observableArray([]);
    this.formErrors = ko.observableArray([]);
    this.loadData = function(next) {
        self.setProcessing(true);
        self.rows([]);
        self.rowsTotal(0);
        $.ajax({
            url: Routing.generate('landlord_reports_review_get_rows'),
            type: 'POST',
            dataType: 'json',
            data: {
                'newRows': next
            },
            error: function() {
                self.setProcessing(false);
                self.errorLoadDataMessage(Translator.trans('import.error.flush'));
            },
            success: function(response) {
                self.setProcessing(false);
                self.errorLoadDataMessage(response.message);
                if (response.error === false) {
                    self.rows(response.rows);
                    self.rowsTotal(response.total);
                    self.initGuiScript();

                    if (self.rows().length <= 0) {
                        self.isFinishReview(true);
                    }
                }
            }
        });
    };

    this.isVisibleTable = function() {
        if (self.errorLoadDataMessage().length <= 0 && self.rows().length > 0) {
            return true;
        }
        return false;
    }

    this.getMoveOut = function(data) {
        if (data.moveOut !== undefined) {
            return data.moveOut;
        }

        return '';
    }
    //Use this function, because extension does not work
    this.initGuiScript = function() {
        $.each($('.datepicker'), function(key, value) {
           $(this).datepicker({
                showOn: "both",
                buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
                format: 'm/d/Y'
            });
        });
    };

    this.removeGuiScript = function() {
        $.each($('.datepicker'), function(key, value) {
            $(this).datepicker("destroy");
        });
    };


    this.getStatusText = function(data) {
        if (data.isSkipped) {
            return Translator.trans('import.status.skip');
        }

        if (!data.isValid) {
            return Translator.trans('import.status.error');
        }

        if (data.Contract.status == 'finished' && self.getMoveOut(data).length > 0) {
            return Translator.trans('import.status.ended');
        }

        if (data.Contract.id !== undefined && data.Tenant.id !== undefined) {
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

    this.submitForms = function() {
        self.removeGuiScript();
        self.setProcessing(true);
        var number = 0;
        var success = Array();
        var errors = Array();
        var forms = Object();

        $.each($('.properties-table tr'), function (key,value) {
            var element = $(this);
            //not allow send knockout duplicate
            if (element.find('td').length <= 0) {
                return;
            }
            var form = new Object();
            if (element.find('form').length > 0) {
                var data = element.find('input').serializeArray();
                $.each(data, function (key,value) {
                    form[value.name] = value.value;
                });
            }
            form['line'] = self.rows()[number].number;
            forms[number] = form;
            number++;
        });

        self.formErrors([]);
        $.ajax({
            url: Routing.generate('landlord_reports_review_save_row'),
            type: 'POST',
            async: true,
            dataType: 'json',
            data: forms,
            error: function() {
                self.setProcessing(false);
                self.errorLoadDataMessage(Translator.trans('import.error.flush'));
            },
            success: function(response) {
                var errorsLen = $.map(response.formErrors, function(n, i) { return i; }).length;
                if (errorsLen > 0) {
                    self.rows(self.rows());
                    self.formErrors(response.formErrors);
                    self.setProcessing(false);
                    self.initGuiScript();
                } else {
                    self.loadData(true);
                }
            }
        });
    }

    this.uniqueId = function() {
        // always start with a letter (for DOM friendlyness)
        var idstr=String.fromCharCode(Math.floor((Math.random()*25)+65));
        do {
            // between numbers and characters (48 is 0 and 90 is Z (42-48 = 90)
            var ascicode=Math.floor((Math.random()*42)+48);
            if (ascicode<58 || ascicode>64){
                // exclude all chars between : (58) and @ (64)
                idstr+=String.fromCharCode(ascicode);
            }
        } while (idstr.length<36);

        return (idstr);
    }

    this.setProcessing = function(newValue) {
        if (newValue) {
            $('#reviewContainer').parent().showOverlay();
            return true;
        }
        $('#reviewContainer').parent().hideOverlay();
        return true;
    };

    this.getErrorsFields = function(data){
        var result = {};
        jQuery.each(data, function(keys1, values1) {
            jQuery.each(values1, function(keys2, values2) {
                if (values2 instanceof Array) {
                    result[keys2] = values2;
                } else {
                    jQuery.each(values2, function (fieldName, errors) {
                        result[fieldName] = errors;
                    });
                }
            })
        });

        return result;
    }

    this.getErrorsList = function(data) {
        var number = data.number;

        if (self.formErrors()[number] === undefined) {
            return '';
        }

        return self.formErrors()[number];
    };

    this.getClassLine = function(data) {
        return 'line_number_'+data.number+' ';
    }

    this.getErrorClass = function(data, nameField) {
        if (!self.getErrorsList(data)) {
            return '';
        }

        var result = self.getErrorsFields(self.getErrorsList(data));

        if (result[nameField] == undefined) {
            return '';
        }

        return 'errorField';
    };

    this.getErrorTitle = function(data, nameField) {
        if (!self.getErrorsList(data)) {
            return;
        }

        var result = self.getErrorsFields(self.getErrorsList(data));

        if (result[nameField] == undefined) {
            return;
        }

        return result[nameField][0];
    };

    this.getUnitClass = function(data) {
        if (data.isSkipped) {
            return '';
        }

        if (data.isValidUnit) {
            return '';
        }

        return 'errorField';
    }

    this.getUnitTitle = function(data) {
        if (data.isSkipped) {
            return '';
        }

        if (data.isValidUnit) {
            return '';
        }

        return Translator.trans('import.error.unit');
    }

    this.getResidentIdClass = function(data) {
        if (data.isSkipped) {
            return '';
        }

        if (data.isValidResidentId) {
            return '';
        }

        return 'errorField';
    }

    this.getResidentIdTitle = function(data) {
        if (data.isSkipped) {
            return '';
        }

        if (data.isValidResidentId) {
            return '';
        }

        return Translator.trans('import.error.residentId');
    }
}
