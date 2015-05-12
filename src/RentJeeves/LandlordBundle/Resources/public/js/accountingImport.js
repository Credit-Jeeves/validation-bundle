function accountingImport(superclass) {
    var self = this;
    this.superclass = superclass;
    this.source = ko.observable('');
    this.importType = ko.observable();
    this.isMultipleProperty = ko.observable(false);
    this.isValidDateFormat = ko.observable(true);
    this.fieldsWhichNotContaintInForm = [
        "import_new_user_with_contract_contract_residentMapping_residentId",
        "import_new_user_with_contract_contract_unitMapping_externalUnitId",
        "import_contract_residentMapping_residentId",
        "resident_id",
        "tenant_email"
    ];

    this.unitName = [
        "import_new_user_with_contract_contract_unit_name",
        "import_contract_unit_name"
    ];

    this.rowsTotal = ko.observable(0);
    this.loadDataMessage = ko.observable('');
    this.showSpinner = ko.observable(false);
    this.classLoadDataMessage = ko.observable('errorMessage');
    this.rows = ko.observableArray([]);
    this.formErrors = ko.observableArray([]);
    this.hasException = ko.observable(false);

    this.loadData = function(next) {
        self.setProcessing(true);
        jQuery.ajax({
            url: Routing.generate('accounting_import_get_rows'),
            type: 'POST',
            dataType: 'json',
            data: {
                'newRows': next
            },
            error: function() {
                self.rows([]);
                self.rowsTotal(0);
                self.setProcessing(false);
                self.loadDataMessage(Translator.trans('import.error.flush'));
            },
            success: function(response) {
                self.hasException(false);
                self.setProcessing(false);
                self.loadDataMessage(response.message);

                if (response.error === false) {
                    var errors = new Array();
                    //Fill error by line
                    ko.utils.arrayForEach(response.rows, function (value) {
                        if (value.is_valid_date_format == false && self.isValidDateFormat() == true) {
                            self.isValidDateFormat(false);
                        }

                        if (value.unique_key_exception != null && self.hasException() == false) {
                           self.hasException(true);
                        }

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
                        self.setProcessing(true);
                        url = Routing.generate(
                            'import_summary_report',
                            {'importSummaryPublicId': response.importSummaryPublicId}
                        );
                        window.location.replace(url);
                    }
                }
            }
        });
    };

    this.skipException = function()
    {
        self.loadData(true);
    }

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
        if (self.loadDataMessage().length == 0 && self.rows().length > 0) {
            return true;
        }
        return false;
    }

    this.getDatePickerOptions = function(disabled) {
        var options = {
            showOn: "both",
            buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
            format: 'm/d/Y',
            disabled: disabled
        };

        return options;
    }

    this.getStatusText = function(data)
    {
        if (data.unique_key_exception != null) {
            return Translator.trans('import.status.exception', {"id":data.unique_key_exception});
        }

        if (data.is_skipped && !self.isValidFieldsWhichNotContainsInForm(data)) {
            return Translator.trans('import.status.error');
        }

        if (data.is_skipped) {
            return Translator.trans('import.status.skip');
        }

        if (!self.isValidFieldsWhichNotContainsInForm(data) || data.contract.property === null) {
            return Translator.trans('import.status.error');
        }

        if (data.contract.status == 'finished') {
            return Translator.trans('import.status.ended');
        }

        if (data.contract.is_late && data.contract.id !== null) {
            return Translator.trans('conflict.resolve.action');
        }

        if (data.contract.id !== null || data.has_contract_waiting) {
            return Translator.trans('import.status.match');
        }

        if (data.tenant.email === null) {
            return Translator.trans('import.status.waiting');
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
        self.hasException(false);

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
                var inputs = element.find('input');
                var disabled = Array();
                //disabled element turn off
                $.each(inputs, function( index, value ) {
                    if ($(this).attr('disabled')) {
                        $(this).attr('disabled', false);
                        disabled.push($(this));
                    }
                });
                var data = inputs.serializeArray();
                ko.utils.arrayForEach(data, function (value) {
                    form[value.name] = value.value;
                });
                //disabled element turn on
                $.each(disabled, function( index, value ) {
                    $(this).attr('disabled', true);
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
                self.loadDataMessage(Translator.trans('import.error.flush'));
            },
            success: function(response) {
                var errorsLen = jQuery.map(response.formErrors, function(n, i) { return i; }).length;
                var rows = $.map(response.rows, function(value, index) {
                    if (value.unique_key_exception != null && self.hasException() == false) {
                        self.hasException(true);
                    }
                    return [value];
                });
                if (rows.length > 0) {
                    self.formErrors(response.formErrors);
                    self.rows(rows);
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
            currentRow.contract[datepickerFieldName] = fullDate;
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

    /**
     * Overwrites obj1's values with obj2's and adds obj2's if non existent in obj1
     * @param obj1
     * @param obj2
     * @returns obj3 a new object based on obj1 and obj2
     */
    this.mergeObject = function(obj1,obj2){
        var obj3 = {};
        for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
        for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
        return obj3;
    }

    this.getErrorsFields = function(data, result){
        if ($.type(result) !== 'object') {
            this.result = {};
        } else {
            this.result = result;
        }
        var self = this;
        //use jquery, because knockout function can't get key of array/object - just value
        jQuery.each(data, function(key, value) {
            var result = self.result;
            if ($.type(value) === 'array') {
                result[key] = value[0];
            } else if ($.type(value) === "string") {
                result[key] = value;
            } else {
                var result2 = self.getErrorsFields(value, result);
                var result = self.mergeObject(result, result2);
            }

            self.result = result;
        });

        return this.result;
    }

    this.getErrorsList = function(data) {
        var number = data.number;

        if (self.formErrors()[number] === undefined) {
            return '';
        }

        return self.formErrors()[number];
    };

    this.getResidentId = function(data)
    {
        if (data.resident_mapping != null) {
            return data.resident_mapping.resident_id;
        }
    }

    this.getClassLine = function(data) {
        return 'line_number_'+data.number+' ';
    }

    this.getErrorClass = function(data, nameField) {
        if (self.isErrorForUnit(data, nameField)) {
            return '';
        }

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
        if (self.isErrorForUnit(data, nameField)) {
            return '';
        }

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

        return errorList[nameField];
    };

    this.isValidFieldsWhichNotContainsInForm = function(data) {
        if (data.contract.property === null) {
            return false;
        }

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

    this.isCheckedInvite = function(email) {
        if (email === null) {
            return false;
        }

        return email.length > 0;
    }

    /**
     * @param data
     * @returns {boolean}
     */
    this.isValidUnitAndIsSingle = function(data) {
        if (!self.getErrorsList(data)) {
            return isValid;
        }

        var isSingle = data.contract.property.is_single;
        var errorList = self.getErrorsFields(self.getErrorsList(data));
        var isValid = true;

        ko.utils.arrayForEach(self.unitName, function (value) {
            if (errorList[value] !== undefined) {
                isValid = false;
            }
        });

        if (isValid) {
            return true;
        }

        if (isSingle) {
            return true;
        }

        return false;
    }

    this.isUnitField = function(fieldName)
    {
        var isUnitField = false;
        ko.utils.arrayForEach(self.unitName, function (value) {
            if (value === fieldName) {
                isUnitField = true;
            }
        });

        return isUnitField;
    }

    this.isErrorForUnit = function(data, nameField)
    {
        if (!self.isUnitField(nameField)) {
            return false;
        }

        if (data.contract.property === null) {
            return false;
        }

        if (data.contract.property.is_single) {
            return true;
        }

        return false;
    }

    this.getUnitName = function(data)
    {
        if (data.contract.unit === null) {
            return '';
        }

        return data.contract.unit.name;
    }
}
