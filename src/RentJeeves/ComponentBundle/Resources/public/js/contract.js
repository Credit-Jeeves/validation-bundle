function Contract() {
    var self = this;
    this.outstandingBalance = ko.observable(0);
    this.propertiesList = ko.observableArray([]);
    this.dueDateList = ko.observableArray([]);
    for(i = 1; i <= 31; i++) {
        var data = {
            'text': i,
            'value': i
        };
        self.dueDateList().push(data);
    }
    this.unitsList = ko.observableArray([]);
    this.currentPropertyId = ko.observable();
    this.currentUnitId = ko.observable();
    this.optionsFinishAt = ko.observable('finishAt');
    this.contract = ko.observable();
    this.approve = ko.observable(false);
    this.review = ko.observable(false);
    this.edit = ko.observable(false);
    this.invite = ko.observable(false);
    this.due = ko.observableArray(['1th', '5th', '10th', '15th', '20th', '25th']);
    this.errorsApprove = ko.observableArray([]);
    this.errorsEdit = ko.observableArray([]);
    this.statusBeforeTriedSave = ko.observable();

    this.cancelEdit = function (data) {
        $('#tenant-edit-property-popup').dialog('close');
        if (self.approve()) {
            self.approveContract(self.contract());
        }
        self.clearDetails();
    };

    this.getUnits = function (propertyId) {
        self.unitsList([]);
        $('#unit-edit').parent().find('.loader').show();
        $.ajax({
            url: Routing.generate('landlord_units_list'),
            type: 'POST',
            dataType: 'json',
            data: {'property_id': propertyId },
            success: function (response) {
                $('#unit-edit').parent().find('.loader').hide();
                self.unitsList(response.units);
                self.currentUnitId(self.contract().unit_id);
            }
        });
    };

    this.onPropertyChange = function (property, event) {
        if (self.currentPropertyId() != undefined) {
            self.getUnits(self.currentPropertyId());
        }
    };

    this.getProperties = function (propertyId) {
        self.propertiesList([]);
        $('#property-edit').parent().find('.loader').show();
        $.ajax({
            url: Routing.generate('landlord_properties_list_all'),
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                $('#property-edit').parent().find('.loader').hide();
                self.propertiesList(response);
            }
        });
    };

    this.closeApprove = function (data) {
        $('#tenant-approve-property-popup').dialog('close');
        return false;
    }

    this.editContract = function (contract) {

        self.errorsApprove([]);
        self.errorsEdit([]);
        $('#unit-edit').html(' ');
        $('#tenant-approve-property-popup').dialog('close');
        $('#tenant-edit-property-popup').dialog('open');

        if (contract.first_name) {
            self.contract(contract);
        }

        if (self.contract().finish.length > 0) {
            self.optionsFinishAt('finishAt');
        } else {
            self.optionsFinishAt('monthToMonth');
        }

        self.currentPropertyId(self.contract().property_id);
        self.getProperties(self.contract().property_id);
        self.getUnits(self.contract().property_id);


        var flag = false;
        if (self.approve()) {
            flag = true;
        }
        self.clearDetails();
        self.edit(true);
        self.approve(flag);
        window.jQuery.curCSS = window.jQuery.css;
        $('#contractEditStart').datepicker({
            showOn: "both",
            buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
            format: 'm/d/Y',
            minDate: 0,
            starts: 1,
            position: 'r',
            onBeforeShow: function () {
                $('#contractEditStart').DatePickerSetDate($('#contract-edit-start').val(), true);
            },
            onChange: function (formated, dates) {
                $('#contractEditStart').val(formated);
                $('#contractEditStart').DatePickerHide();
            }
        });
        $('#contractEditFinish').datepicker({
            showOn: "both",
            buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
            format: 'm/d/Y',
            minDate: 0,
            starts: 1,
            position: 'r',
            onBeforeShow: function () {
                $('#contractEditFinish').DatePickerSetDate($('#contract-edit-finish').val(), true);
            },
            onChange: function (formated, dates) {
                $('#contractEditFinish').val(formated);
                $('#contractEditFinish').DatePickerHide();
            }
        });
    };

    this.approveContract = function (contract) {
        self.contract(contract);
        self.errorsApprove([]);
        self.errorsEdit([]);
        $('#unit-edit').html(' ');
        $('#tenant-approve-property-popup').dialog('open');
        self.clearDetails();

        self.approve(true);
        $('#contractApproveStart').attr('readonly', true);
        $('#contractApproveFinish').attr('readonly', true);

        if ($('#contractApproveStart').val().length > 0) {
            var start = $('#contractApproveStart').val();
        } else {
            var today = new Date();
            var start = today.toString('MM/dd/yyyy');
        }

        if ($('#contractApproveFinish').val().length > 0) {
            var finish = $('#contractApproveFinish').val();
        } else {
            var today = new Date();
            today.setFullYear(today.getFullYear() + 1);
            var finish = today.toString('MM/dd/yyyy');
        }

        contract.start = start;
        contract.finish = finish;
        self.contract(contract);

        $('#contractApproveStart').datepicker({
            showOn: "both",
            buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
            format: 'm/d/Y',
            starts: 1,
            minDate: 0,
            position: 'r',
            onChange: function (formated, dates) {
                $('#contractApproveStart').val(formated);
                $('#contractApproveStart').DatePickerHide();
            }
        });
        $('#contractApproveFinish').datepicker({
            showOn: "both",
            buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
            format: 'm/d/Y',
            starts: 1,
            minDate: 0,
            position: 'r',
            onChange: function (formated, dates) {
                $('#contractApproveFinish').val(formated);
                $('#contractApproveFinish').DatePickerHide();
            }
        });
    };

    this.countErrorsEdit = ko.computed(function () {
        return parseInt(self.errorsEdit().length);
    });

    this.countErrorsApprove = ko.computed(function () {
        return parseInt(self.errorsApprove().length);
    });

    this.reviewContract = function (data) {
        $('#unit-edit').html(' ');
        $('#tenant-review-property-popup').dialog('open');
        self.clearDetails();
        self.approve(false);
        self.contract(data);
        self.review(true);
    };

    this.approveSave = function () {
        var data = self.contract();
        self.statusBeforeTriedSave(data.status);
        data.status = 'approved';
        self.contract(data);
        self.saveContract();
    };

    this.removeContractConfirm = function () {
        $('#contract-remove-popup').hideOverlay();
        $('#contract-remove-popup').dialog('open');
        $('#tenant-edit-property-popup').dialog('close');
    };

    this.cancelRemove = function() {
        $('#contract-remove-popup').dialog('close');
        $('#tenant-edit-property-popup').dialog('open');
    };

    this.removeContract = function() {
        $('#contract-remove-popup').showOverlay();
        var data = self.contract();
        data.action = 'remove';
        self.contract(data);
        self.saveContract(function(){
            $('#contract-remove-popup').hideOverlay();
            $('#contract-remove-popup').dialog('close');
        });
    };

    this.clearDetails = function () {
        self.edit(false);
        self.review(false);
        self.approve(false);
    };

    this.saveContract = function (callback) {
        if (self.edit()) {
            var id = '#tenant-edit-property-popup';
        } else {
            var id = '#tenant-approve-property-popup';
        }

        jQuery(id).showOverlay();
        var contract = self.contract();

        if (self.currentUnitId() != undefined) {
            contract.unit_id = self.currentUnitId();
        }

        if (self.currentPropertyId() != undefined) {
            contract.property_id = self.currentPropertyId();
        }

        if (self.optionsFinishAt() === 'monthToMonth') {
            contract.finish = null;
        }

        self.contract(contract);
        $.ajax({
            url: Routing.generate('landlord_contract_save'),
            type: 'POST',
            dataType: 'json',
            data: {
                'contract': self.contract()
            },
            success: function (response) {
                //TODO remove this code and use callback
                jQuery(id).hideOverlay();
                self.errorsApprove([]);
                self.errorsEdit([]);
                if (typeof response.errors == 'undefined') {
                    $('#tenant-edit-property-popup').dialog('close');
                    $('#tenant-approve-property-popup').dialog('close');
                    self.clearDetails();
                    ContractsViewModel.ajaxAction();
                } else {
                    if (self.edit()) {
                        self.editContract(self.contract());
                        self.errorsEdit(response.errors);
                    } else {
                        if (self.contract().status == 'approved') {
                            self.contract().status = self.statusBeforeTriedSave();
                            self.approveContract(self.contract());
                        }
                        self.errorsApprove(response.errors);
                    }
                }
                //end TODO
                if (typeof callback === 'function' ) {
                    callback(response);
                }
            }
        });
    };
    this.revokeInvitation = function () {
        jQuery('#tenant-revoke-invotation').showOverlay();
        $.ajax({
            url: Routing.generate('revoke_invitation', {'contractId': self.contract().id }),
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                jQuery('#tenant-revoke-invotation').hideOverlay();
                if (typeof response.error !== 'undefined') {
                    $('#tenant-review-property-popup').find('.error').html(response.error);
                    $('#tenant-review-property-popup').find('.error').show();
                } else {
                    $('#tenant-review-property-popup').find('.error').hide();
                    $('#tenant-revoke-invotation').dialog('close');
                    self.clearDetails();
                    ContractsViewModel.ajaxAction();
                }
            }
        });
    };
    this.closeRevokeInvitation = function () {
        $('#tenant-edit-property-popup').dialog('open');
        $('#tenant-revoke-invotation').dialog('close');
        return false;
    }

    this.closeReminderRevoke = function () {
        $('#tenant-edit-property-popup').dialog('close');
        $('#tenant-revoke-invotation').dialog('open');
        return false;
    }
    this.closeTenantReviewPropertyPopup = function () {
        $('#tenant-review-property-popup').dialog('close');
        return false;
    }
    this.sendReminderInvition = function () {
        jQuery('#tenant-edit-property-popup').showOverlay();
        $.ajax({
            url: Routing.generate('send_reminder_invitation', {'contractId': self.contract().id }),
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                jQuery('#tenant-review-property-popup').hideOverlay();
                if (typeof response.error !== 'undefined') {
                    self.errorsEdit.push(response.error);
                } else {
                    self.errorsEdit([]);
                }
                jQuery('#tenant-edit-property-popup').hideOverlay();
            }
        });
    };

    this.openEndContractPopup = function (contract) {
        $('#tenant-edit-property-popup').dialog('close');
        $('#tenant-end-contract').dialog('open');
        return false;
    }

    this.endContractExecute = function () {
        jQuery('#tenant-end-contract').showOverlay();
        $.ajax({
            url: Routing.generate('landlord_end_contract', {'contractId': self.contract().id }),
            type: 'POST',
            data: {'uncollectedBalance': self.outstandingBalance() },
            dataType: 'json',
            success: function (response) {
                jQuery('#tenant-end-contract').hideOverlay();
                self.clearDetails();
                $('#tenant-end-contract').dialog('close');
                ContractsViewModel.ajaxAction();
                self.outstandingBalance(0);
            }
        });
    }

    this.leaveIntact = function (contract) {
        $('#tenant-edit-property-popup').dialog('open');
        $('#tenant-end-contract').dialog('close');
        return false;
    }
}
