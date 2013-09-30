function Pay(parent, contractId) {
    ko.cleanNode($('#pay-popup').get(0));

    var self = this;
    var contract = parent.getContractById(contractId);
    var current = 1;
    var steps = ['details', 'source', 'user', 'questions', 'pay'];
    var forms = {
        'details': 'rentjeeves_checkoutbundle_paymenttype',
        'source': 'rentjeeves_checkoutbundle_paymentaccounttype',
        'user': 'rentjeeves_checkoutbundle_userdetailstype',
        'questions': 'questions'
    };

    if ('passed' == parent.verification) {
        steps.splice(2, 2);
    }
    this.step = ko.observable('source');
    this.step.subscribe(function(newValue) {
        switch (newValue) {
            case 'details':
                break;
            case 'source':
                break;
            case 'user':
                break;
            case 'questions':
                if (parent.questions) {
                    break;
                }

                jQuery('#pay-popup').showOverlay();
                jQuery.get(Routing.generate('experian_pidkiq_get'), '', function(data, textStatus, jqXHR) {
                    if (data['status'] && 'error' == data['status']) {
                        addFormError(null, data['error']);
                        return;
                    }
                    parent.questions = data; //TODO add identity check
                    self.questions(data);

                    jQuery('#pay-popup').hideOverlay();
                });
                break;
            case 'pay':
                break;
        }
    });

    var startDate = new Date(contract.start_at);
    startDate.setDate(startDate.getDate() + 1);

    var finishDate = new Date(contract.finish_at);



    /*  Form fields  */
    this.amount = ko.observable(contract.amount);
    this.type = ko.observable('recurring');
    this.frequency = ko.observable('monthly');
    this.frequency.subscribe(function(newValue) {
        if ('month_last_date' == newValue) {
            this.dueDate(31);
        } else {
            this.dueDate(startDate.getDate());
        }
    }, this);
    this.dueDate = ko.observable(startDate.getDate());
    this.startMonth = ko.observable(startDate.getMonth());
    this.startYear = ko.observable(startDate.getYear());
    this.startDate = ko.observable(startDate.toString('MM/dd/yyyy'));
    this.ends = ko.observable('cancelled');
    this.endMonth = ko.observable(finishDate.getMonth() + 1);
    this.endYear = ko.observable(finishDate.getYear());

    this.propertyFullAddress = new Address(this, window.addressesViewModels);
    this.propertyFullAddress.street(contract.address);
    this.propertyFullAddress.city(contract.city);
    this.propertyFullAddress.zip(contract.zip);
    this.propertyFullAddress.area(contract.area);
    /* /Form fields/ */


    this.propertyAddress = ko.observable(this.propertyFullAddress.toString());
    this.fullPayTo = contract.full_pay_to;
    this.settleDays = 3; // All logic logic in "settle" method depends on this value
    this.settle = ko.computed(function() {
        var settleDate = new Date(this.startDate());
        var startDayOfWeek = (0 == settleDate.getDay()?7:settleDate.getDay()); // Move Sunday from 0 to 7
        /* logic: skip weekends */
        var daysAdd = (4 == startDayOfWeek || 6 == startDayOfWeek ? 1 : 0);
        if (0 == daysAdd) {
            daysAdd = (5 == startDayOfWeek ? 2 : 0);
        }
        /* end of logic: skip weekends */

        settleDate.add(/*this.settleDays*/3).days();// see comment of this.settleDays
        var dayOfWeek = (0 == settleDate.getDay()?7:settleDate.getDay()); // Move Sunday from 0 to 7
        var daysShift = 8 - dayOfWeek; // Settle day can't be weekend
        if (2 < daysShift) {
            daysShift = 0;
        }
        settleDate.add(daysShift + daysAdd).days();
        return settleDate.toString('MM/dd/yyyy');
    }, this);
    this.getLastPaymentDay = ko.computed(function() {
        var finishDate = new Date(contract.finish_at);
        if ('on' == this.ends()) {
            finishDate.setMonth(this.endMonth() - 1);
            finishDate.setYear(this.endYear());
            finishDate.setDate(
                this.dueDate() > finishDate.getDaysInMonth() ?
                    finishDate.getDaysInMonth() :
                    this.dueDate()
            );
        }
        return finishDate.toString('MM/dd/yyyy');
    }, this);

    this.paymentSource = new PaymentSource(this, false);

    this.address = new Address(this, window.addressesViewModels, this.propertyFullAddress);
    this.questions = ko.observable(parent.questions);




    this.getAmount = ko.computed(function() {
        return '$' + this.amount();
    }, this);
    this.isForceSave = ko.computed(function() {
        var result = 'immediate' != this.type();
        this.paymentSource.save(result);
        this.paymentSource.isForceSave(result);
        return result;
    }, this);


    this.stepExist = function(step) {
        return -1 != steps.indexOf(step);
    };

    var onSuccessStep = function(data) {
        var currentStep = steps[current];
        switch (currentStep) {
            case 'details':
                break;
            case 'source':
                break;
            case 'user':
                break;
            case 'questions':
                parent.verification = data.verification;
                steps.splice(2, 2);
                current -= 2;
                break;
            case 'pay':
                break;
        }

        self.step(steps[++current]);
    };

    var sendData = function(url, formId) {


        // HTML5 validation // TODO fix for hidden fields
//        try {
//            jQuery('#' + formId + '_submit').click();
//            if (!jQuery('#' + formId)[0].checkValidity()) {
//                return false;
//            }
//        } catch (e) {
//            alert(e);
//            // form have invalid but hidden fields
//        }

//        if (!formValidator.submit()) {// TODO implement after bug fixes
//            console.log('submit fail');
//            return false;
//        }
        jQuery('#pay-popup').showOverlay();

        var data = jQuery('#' + formId).serializeArray();

        jQuery.ajax({
            url: url,
            type: 'POST',
            timeout: 30000, // 30 secs
            dataType: 'json',
            data: jQuery.param(data, false),
//            complete: function(jqXHR, textStatus) {
//                jQuery('#pay-popup').hideOverlay();
//            },
            error: function(jqXHR, textStatus, errorThrown) {
                removeAllErrors();
                jQuery('#pay-popup').hideOverlay();
                if ('<!DOCTYPE' == jqXHR.responseText.substr(0, 9)) {
                    jQuery('body').showOverlay();
                    window.location.reload();
                }
                addFormError(null, errorThrown);
            },
            success: function(data, textStatus, jqXHR) {
                removeAllErrors();
                jQuery.each(forms, function(key, formName) {
                    $('#' + formName + ' .error').removeClass('error');
                });

                jQuery('#pay-popup').hideOverlay();
                if (!data.success) {
                    applyErrors(data);
                    return;
                }
                onSuccessStep(data);
            }
        });
    };

    var applyErrorsToField = function(formName, fieldName, errors) {
        var formValidator = jsfv[formName];
        jQuery.each(errors, function(key, error) {
            if ('[object Array]' === Object.prototype.toString.call(error)) {
                applyErrorsToField(formName, key, error);
            } else {
                if (field = formValidator.id(fieldName)) {
                    formValidator.addError(field, error);
                } else {
                    formValidator.addError(formValidator.id(formName), error);
                }
            }
        });
    };

    var removeAllErrors = function() {
        $('#pay-popup .attention-box').hide();
        $('#pay-popup .attention-box ul li').remove();
    };

    var applyErrors = function(data) {
        jQuery.each(data, function(formName, fields) {
            jQuery.each(fields, function(fieldName, errors) {
                applyErrorsToField(formName, fieldName, errors);
            });
        });
    };

    this.next = function() {
        var currentStep = steps[current];

        switch (currentStep) {
            case 'details':
                sendData(Routing.generate('checkout_pay_payment'), forms[currentStep]);
                break;
            case 'source':
                sendData(Routing.generate('checkout_pay_source'), forms[currentStep]);
                break;
            case 'user':
                sendData(Routing.generate('checkout_pay_user'), forms[currentStep]);
                break;
            case 'questions':
                sendData(Routing.generate('experian_pidkiq_execute'), forms[currentStep]);
                break;
            case 'pay':
//                sendData(Routing.generate('checkout_pay_user'), forms[currentStep]);
                $('#pay-popup').dialog('close');
                jQuery('body').showOverlay();
                window.location.reload();
                break;
        }

    };

    this.previous = function() {
        current--;
        removeAllErrors();
        this.step(steps[current]);
    };

    // Constructor

    $("#pay-popup").dialog({
        width:650,
        modal:true
    });


    $("input.datepicker-field").datepicker({
        showOn: "button",
        buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
        buttonImageOnly: true,
        showOtherMonths: true,
        selectOtherMonths: true
    });

//    $("#vi-questions").parent().replaceWith($("#vi-questions"));
//    $('#vi-questions').slimScroll({
//        alwaysVisible:true,
//        width:330,
//        height:260
//    });

    $('.user-ssn').ssn();

    ko.applyBindings(this, $('#pay-popup').get(0));

    var addFormError = function(field, errorMessage) {
        $('#pay-popup .attention-box').show();
        // Add errors block
        $(field).parents('.form-row').addClass('error');
        $(field).addClass('error');


        // Add error
        $('#pay-popup .attention-box ul').append('<li>'+errorMessage+'</li>');
    };

    jQuery.each(forms, function(key, formName) {
        jsfv[formName].addError = addFormError;
        jsfv[formName].removeErrors = function(field) {};
        jQuery('#' + formName).submit(function() {
            self.next();
            return false;
        });
    });

    removeAllErrors();
}
