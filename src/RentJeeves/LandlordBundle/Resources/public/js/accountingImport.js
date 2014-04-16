function accountingImport() {
    var self = this;
    this.fieldsWhichNotContaintInForm = [
        "import_new_user_with_contract_contract_residentMapping_residentId",
        "import_new_user_with_contract_contract_unit_name",
        "import_contract_unit_name",
        "import_contract_residentMapping_residentId"
    ];
    this.rowsTotal = ko.observable(0);
    this.errorLoadDataMessage = ko.observable('');
    this.isFinishReview =  ko.observable(false);
    this.rows = ko.observableArray([]);
    this.formErrors = ko.observableArray([]);
    this.loadData = function(next) {
        self.setProcessing(true);
        self.rows([]);
        self.rowsTotal(0);
        jQuery.ajax({
            url: Routing.generate('accounting_import_get_rows'),
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
                    var errors = new Array();
                    //Fill error by line
                    ko.utils.arrayForEach(response.rows, function (value) {
                        if (value.errors.length === 0) {
                            return;
                        }

                        errors[value.number] = value.errors[value.number];
                    });
                    //Finish
                    self.formErrors(errors);
                    self.rows(response.rows);
                    self.rowsTotal(response.total);

                    if (self.rows().length == 0) {
                        self.isFinishReview(true);
                    }
                }
            }
        });
    };

    this.uniqueId = function() {
        // always start with a letter (for DOM friendliness)
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

    this.isVisibleTable = function() {
        if (self.errorLoadDataMessage().length == 0 && self.rows().length > 0) {
            return true;
        }
        return false;
    }

    this.getDatePickerOptions = function() {
        var options = {
            showOn: "both",
            buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
            format: 'm/d/Y'
        };

        return options;
    }

    this.getStatusText = function(data) {
        if (data.is_skipped) {
            return Translator.trans('import.status.skip');
        }

        if (!self.isValidFieldsWhichNotContainsInForm(data)) {
            return Translator.trans('import.status.error');
        }

        if (data.contract.status == 'finished' && data.move_out !== null) {
            return Translator.trans('import.status.ended');
        }

        if (data.contract.is_late && data.operation !== null) {
            return Translator.trans('conflict.resolve.action');
        }

        if (data.contract.id !== null) {
            return Translator.trans('import.status.match');
        }

        return Translator.trans('import.status.new');
    };

    this.formatDate = function(dateString) {
        if (!dateString || dateString.length == 0) {
            return '';
        }
        var date = new Date(dateString);
        return (date.getMonth() + 1) + '/' + date.getDate() + '/' +  date.getFullYear();
    }

    this.submitForms = function() {
        self.setProcessing(true);
        var number = 0;
        var success = Array();
        var errors = Array();
        var forms = Object();

        ko.utils.arrayForEach(jQuery('.properties-table tr'), function (value) {
            var element = jQuery(value);
            //not allow send knockout duplicate
               if (element.find('td').length == 0) {
                return;
            }
            var form = new Object();
            if (element.find('form').length > 0) {
                var data = element.find('input').serializeArray();
                ko.utils.arrayForEach(data, function (value) {
                    form[value.name] = value.value;
                });
            }
            form['line'] = self.rows()[number].number;
            forms[number] = form;
            number++;
        });

        self.formErrors([]);
        jQuery.ajax({
            url: Routing.generate('accounting_import_save_rows'),
            type: 'POST',
            async: true,
            dataType: 'json',
            data: forms,
            error: function() {
                self.setProcessing(false);
                self.errorLoadDataMessage(Translator.trans('import.error.flush'));
            },
            success: function(response) {
                var errorsLen = jQuery.map(response.formErrors, function(n, i) { return i; }).length;
                if (errorsLen > 0) {
                    self.rows(self.rows());
                    self.formErrors(response.formErrors);
                    self.setProcessing(false);
                } else {
                    self.loadData(true);
                }
            }
        });
    }


    this.setDateDatepickerIntoRow = function(currentRow, elementHtml, datepickerFieldName){
        try {
            jQuery(elementHtml).datepicker("getDate");
            var day = jQuery(elementHtml).datepicker('getDate').getDate();
            var month = jQuery(elementHtml).datepicker('getDate').getMonth() + 1;
            var year = jQuery(elementHtml).datepicker('getDate').getFullYear();
            var fullDate = month + "/" + day + "/" + year;
            if (datepickerFieldName == 'paid_for') {
                currentRow.operation[datepickerFieldName] = fullDate;
            } else {
                currentRow.contract[datepickerFieldName] = fullDate;
            }
        } catch (e) {
            currentRow.contract[datepickerFieldName] = '';
        }
    };

    this.setProcessing = function(newValue) {
        if (newValue && jQuery('.overlay-trigger').length <= 0) {
            jQuery('#reviewContainer').parent().showOverlay();
            return true;
        }

        if (newValue) {
            return true;
        }

        jQuery('#reviewContainer').parent().hideOverlay();
        return true;
    };

    //TODO find out better way
    this.getErrorsFields = function(data){
        var result = {};
        //use jquery, because knockout function can't get key of array/object - just value
        jQuery.each(data, function(keys1, values1) {
            jQuery.each(values1, function(keys2, values2) {
                if (values2 instanceof Array) {
                    result[keys2] = values2;
                    return;
                }

                jQuery.each(values2, function (fieldName, errors) {
                    if (errors instanceof Array) {
                        result[fieldName] = errors;
                        return;
                    }

                    jQuery.each(errors, function (fieldName2, errors2) {
                        result[fieldName2] = errors2;
                        return;
                    });
                });
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
        if (nameField instanceof Array) {
            var errorClass = '';
            ko.utils.arrayForEach(nameField, function (value) {
                var result = self.getErrorClass(data, value);
                if (result.length > 0) {
                    errorClass = result;
                }
            });

            return errorClass;
        }

        if (!self.getErrorsList(data)) {
            return '';
        }

        var result = self.getErrorsFields(self.getErrorsList(data));

        if (result[nameField] === undefined) {
            return '';
        }

        return 'errorField';
    };

    this.getErrorTitle = function(data, nameField) {
        if (nameField instanceof Array) {
            var title = '';
            ko.utils.arrayForEach(nameField, function (value) {
                var result = self.getErrorTitle(data, value);
                if (result.length > 0) {
                    title = result;
                }
            });

            return title;
        }

        if (!self.getErrorsList(data)) {
            return '';
        }

        var errorList = self.getErrorsFields(self.getErrorsList(data));

        if (errorList[nameField] === undefined) {
            return '';
        }

        return errorList[nameField][0];
    };

    this.isValidFieldsWhichNotContainsInForm = function(data) {
        var isValid = true;

        if (!self.getErrorsList(data)) {
            return isValid;
        }

        var errorList = self.getErrorsFields(self.getErrorsList(data));
        var isValid = true;

        ko.utils.arrayForEach(self.fieldsWhichNotContaintInForm, function (value) {
            if (errorList[value] !== undefined) {
                isValid = false;
            }
        });

        return isValid
    }
}
