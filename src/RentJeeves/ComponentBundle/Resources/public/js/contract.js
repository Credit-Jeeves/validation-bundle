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
    this.optionsFinishAtEdit = ko.observable('finishAt');
    this.optionsFinishAtApprove = ko.observable('finishAt');
    this.optionsFinishAtMerge = ko.observable('finishAt');
    this.contract = ko.observable();
    this.approve = ko.observable(false);
    this.review = ko.observable(false);
    this.edit = ko.observable(false);
    this.invite = ko.observable(false);
    this.due = ko.observableArray(['1th', '5th', '10th', '15th', '20th', '25th']);
    this.errorsApprove = ko.observableArray([]);
    this.notificationsEdit = ko.observableArray([]);
    this.errorsMerging = ko.observableArray([]);
    this.errorsEdit = ko.observableArray([]);
    this.errorsAdd = ko.observableArray([]);
    this.statusBeforeTriedSave = ko.observable();
    this.isSingleProperty = ko.observable(true);

    this.mergedContract = ko.observable();
    this.shouldMerge = ko.observable(true);
    this.duplicateContractMessage = ko.observable('');
    this.duplicateContractMatchType = ko.observable(null);
    this.duplicateContractUser = ko.observable(null);

    this.paymentAcceptedMessage = ko.pureComputed(function () {
        if (this.contract()) {
            return Translator.trans('contract.payment_accepted.' + this.contract().paymentAccountingAccepted);
        }

        return '';
    }, this);

    this.debug = false;

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
                self.isSingleProperty(response.isSingle == true);
                self.currentUnitId(self.contract().unit_id);
            }
        });
    };

    this.getProperties = function (propertyId, elementSelect) {
        self.propertiesList([]);
        elementSelect.parent().find('.loader').show();
        $.ajax({
            url: Routing.generate('landlord_properties_list_all'),
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                elementSelect.parent().find('.loader').hide();
                self.propertiesList(response);
                self.enableCurrentPropertyIdSubscription(true);
                self.currentPropertyId(propertyId);
            }
        });
    };

    /*
     * Enable loading list of units from server every time the currentPropertyId variable is set
     *
     * We wrap the "subscribe" call in this function so we can control when events can occur. It was added to
     * avoid prematurely loading the units list when currentPropertyId is initially set in the twig template.
     * It is intended to be chained inside the success callback for getProperties() are loaded and
     * currentPropertyId is initially set.
     *
     * This is a stand-alone function so we can manage creation and deletion of the subscribe closure in one
     * place.
     *
     * If enable is
     *   true : then when currentPropertyId changes, unit list for that property will be loaded.
     *   false : disable loading of unit list on change and dispose of subscription to avoid memory leaks.
     */
    this.enableCurrentPropertyIdSubscription = function(enable) {
        if (enable) {
            if (this.currentPropertyIdSubscription) {
                // noop -- already subscribed.
                this.debug && console.debug("CurrentPropertyIdSubscription Already Subscribed.");
            } else {
                this.debug && console.debug("CurrentPropertyIdSubscription Subscription enabled.");
                this.currentPropertyIdSubscription =
                    this.currentPropertyId.subscribe(function (newValue) {
                        self.getUnits(newValue);
                    });
                /*
                 * RT-1272 : When you edit an invite, the property ID is set, but does not change value
                 * so set this to always notify, which will still load the units list in this situation.
                 */
                this.currentPropertyId.extend({ notify: 'always' }); // RT-1272 : always send event if set!
            }
        } else {
            if (this.currentPropertyIdSubscription) {
                this.debug && console.debug("CurrentPropertyIdSubscription Subscription disabled.");
                this.currentPropertyIdSubscription.dispose();  // remove closure
                this.currentPropertyIdSubscription = null;
            }
        }
    };


    this.closeApprove = function (data) {
        $('#tenant-approve-property-popup').dialog('close');
        return false;
    };

    this.editContract = function (contract) {

        self.errorsApprove([]);
        self.errorsEdit([]);
        self.notificationsEdit([]);
        $('#unit-edit').html(' ');
        $('#tenant-approve-property-popup').dialog('close');
        $('#tenant-edit-property-popup').dialog('open');

        if (contract.first_name) {
            self.contract(contract);
        }

        if (self.contract().finish != null && self.contract().finish.length > 0) {
            self.optionsFinishAtEdit('finishAt');
        } else {
            self.optionsFinishAtEdit('monthToMonth');
        }

        self.getProperties(self.contract().property_id, $("#property-edit"));

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
        self.initControllers();
    };

    this.initializeMergingContractsDialog = function () {
        $('#contract-duplicate-popup').dialog('close');
        self.getProperties(self.contract().property_id, $("#property-merge"));
        $('#contract_start-merge').datepicker({
            showOn: "both",
            buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
            format: 'm/d/Y',
            minDate: 0,
            starts: 1,
            position: 'r',
            onChange: function (formated, dates) {
                $('#contract_start-merge').val(formated);
                $('#contract_start-merge').DatePickerHide();
            }
        });
        $('#contract_finish-merge').datepicker({
            showOn: "both",
            buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
            format: 'm/d/Y',
            minDate: 0,
            starts: 1,
            position: 'r',
            onChange: function (formated, dates) {
                $('#contract_finish-merge').val(formated);
                $('#contract_finish-merge').DatePickerHide();
            }
        });

        $('#tenant-merge-contract-popup').dialog('open');
    };

    this.prepareToMergeContracts = function (contractMergingData) {
        self.errorsMerging([]);
        self.shouldMerge(true);
        if (contractMergingData.matchingType == 'none') {
            self.shouldMerge(false);
            self.duplicateContractMessage(Translator.trans('contract.merging.failure.description'));
        } else {
            self.mergedContract(contractMergingData);
            self.duplicateContractMatchType(contractMergingData.matchingType);
            self.duplicateContractUser(contractMergingData.duplicateTenantInfo);
            self.duplicateContractMessage(Translator.trans('contract.merging.duplicate_found.description'));
        }
        $('#tenant-edit-property-popup').dialog('close');
        $('#tenant-approve-property-popup').dialog('close');
        $('#contract-duplicate-popup').dialog('open');
    };

    this.cancelMergeContract = function () {
        self.duplicateContractMessage(Translator.trans('contract.merging.cancel.description'));
        $('#tenant-merge-contract-popup').dialog('close');
        self.shouldMerge(false);
        $('#contract-duplicate-popup').dialog('open');
    };

    this.closeMergingContractsDialog = function () {
        $('#contract-duplicate-popup').dialog('close');
    };

    this.revertMergingContractsEvent = function () {
        if (self.shouldMerge()) {
            return;
        }
        if (self.approve()) {
            self.approveContract(self.contract());
        } else {
            self.editContract(self.contract());
        }
    };

    this.approveContract = function (contract) {
        self.contract(contract);
        self.errorsApprove([]);
        self.errorsEdit([]);
        self.notificationsEdit([]);
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

    this.countNotificationsEdit = ko.computed(function () {
        return parseInt(self.notificationsEdit().length);
    });

    this.countErrorsEdit = ko.computed(function () {
        return parseInt(self.errorsEdit().length);
    });

    this.countErrorsApprove = ko.computed(function () {
        return parseInt(self.errorsApprove().length);
    });

    this.countErrorsAdd = ko.computed(function () {
        return parseInt(self.errorsAdd().length);
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
        self.enableCurrentPropertyIdSubscription(false);
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

        if (self.edit()) {
            if (self.optionsFinishAtEdit() === 'monthToMonth') {
                contract.finish = null;
            }
        } else {
            if (self.optionsFinishAtApprove() === 'monthToMonth') {
                contract.finish = null;
            }
        }


        self.contract(contract);
        self.initControllers();
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
                self.notificationsEdit([]);
                if (response.mergingData) {
                    self.prepareToMergeContracts(response.mergingData);
                } else if (typeof response.errors == 'undefined') {
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

    this.mergeContract = function () {
        self.errorsMerging([]);
        $("#tenant-merge-contract-popup").showOverlay();
        var mergedContract = self.mergedContract();

        if (self.currentUnitId() != undefined) {
            mergedContract.unitId = self.currentUnitId();
        }

        if (self.currentPropertyId() != undefined) {
            mergedContract.propertyId = self.currentPropertyId();
        }

        if (self.optionsFinishAtMerge() === 'monthToMonth') {
            mergedContract.finish = null;
        }

        self.mergedContract(mergedContract);
        $.ajax({
            url: Routing.generate('landlord_contract_merge'),
            type: 'POST',
            dataType: 'json',
            data: {
                'mergingData': self.mergedContract(),
                'originalContractId': self.contract().id,
                'duplicateContractId': self.mergedContract().duplicateContractId
            },
            success: function (response) {
                self.initializeMergingContractsDialog();
                $("#tenant-merge-contract-popup").hideOverlay();

                if (typeof response.errors == 'undefined') {
                    $('#tenant-merge-contract-popup').dialog('close');
                    self.clearDetails();
                    ContractsViewModel.ajaxAction();
                } else {
                    self.errorsMerging(response.errors);
                }
            },
            error: function() {
                self.initializeMergingContractsDialog();
                $("#tenant-merge-contract-popup").hideOverlay();
                self.errorsMerging([
                    Translator.trans('contract.merging.failed')
                ])
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
    };

    this.closeReminderRevoke = function () {
        $('#tenant-edit-property-popup').dialog('close');
        $('#tenant-revoke-invotation').dialog('open');
        return false;
    };

    this.closeTenantReviewPropertyPopup = function () {
        $('#tenant-review-property-popup').dialog('close');
        return false;
    };

    this.sendReminderInvition = function () {
        jQuery('#tenant-edit-property-popup').showOverlay();
        $.ajax({
            url: Routing.generate('send_reminder_invitation', {'contractId': self.contract().id }),
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                jQuery('#tenant-review-property-popup').hideOverlay();
                self.notificationsEdit([]);
                if (typeof response.error !== 'undefined') {
                    self.errorsEdit.push(response.error);
                    self.notificationsEdit([]);
                } else {
                    self.errorsEdit([]);
                    self.notificationsEdit.push(Translator.trans('contract.reminder.sent.successfully'));
                }
                jQuery('#tenant-edit-property-popup').hideOverlay();
            }
        });
    };

    this.openEndContractPopup = function (contract) {
        $('#tenant-edit-property-popup').dialog('close');
        $('#tenant-end-contract').dialog('open');
        return false;
    };

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
    };

    this.leaveIntact = function (contract) {
        $('#tenant-edit-property-popup').dialog('open');
        $('#tenant-end-contract').dialog('close');
        return false;
    };

    this.saveTenant = function () {
        var id = '#tenant-add-property-popup';
        $(id).showOverlay();
        var formData = $("form#rentjeeves_landlordbundle_invitetenantcontracttype").serialize();
        var url = $('form#rentjeeves_landlordbundle_invitetenantcontracttype').attr('action') ;

        $.ajax({
            url: url,
            type: 'POST',
            timeout: 60000, // 30 secs
            dataType: 'json',
            data: formData,
            success: function(response, textStatus, jqXHR) {
                $(id).hideOverlay();
                if (typeof response.errors == 'undefined') {
                    $('#tenant-add-property-popup').dialog('close');
                    $("form#rentjeeves_landlordbundle_invitetenantcontracttype")[0].reset();
                    DetailsViewModel.errorsAdd([]);
                    ContractsViewModel.ajaxAction();
                } else {
                    DetailsViewModel.errorsAdd(response.errors);
                }
            }
        });

        return false;
    };

    this.initControllers = function () {
        $('#payment_allowed_help')
            .tooltip({
                items: 'i',
                content: Translator.trans('contract.payment_allowed.help_text'),
                position: { my: 'left center', at: 'right+30 center' }
            })
            .off("mouseover")
            .on("click", function () {
                $(this).tooltip("open");

                return false;
            });
        $('.toggle').toggles({
            text: {
                on: Translator.trans('contract.payment_allowed.true'),
                off: Translator.trans('contract.payment_allowed.false')
            },
            on: self.contract().isPaymentAllowed
        });
        $('.toggle').on('toggle', function(e, active) {
            if (active) {
                self.contract().isPaymentAllowed = true;
            } else {
                self.contract().isPaymentAllowed = false;
            }
        });
    };
}
