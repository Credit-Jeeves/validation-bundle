var ContractsViewModel = new Contracts();
var DetailsViewModel = new Contract();

$(document).ready(function () {
    var idProperty = '#rentjeeves_landlordbundle_invitetenantcontracttype_contract_property';

    ko.applyBindings(ContractsViewModel, $('#contracts-block').get(0));
    ko.applyBindings(DetailsViewModel, $('#contract-actions').get(0));
    $('#tenant-approve-property-popup').dialog({
        position: ["center", 200],
        autoOpen: false,
        resizable: false,
        modal: true,
        width: '520px',
        close: function () {
            ContractsViewModel.needRefresh().push(DetailsViewModel.contract().id);
        }
    });

    $('#tenant-edit-property-popup').dialog({
        position: "center",
        autoOpen: false,
        resizable: false,
        modal: true,
        width: '520px',
        close: function () {
            ContractsViewModel.needRefresh().push(DetailsViewModel.contract().id);
        }
    });

    $('#tenant-merge-contract-popup').dialog({
        position: "center",
        autoOpen: false,
        resizable: false,
        modal: true,
        width: '520px',
        close: function () {
        }
    });

    $('#tenant-review-property-popup').dialog({
        position: "center",
        autoOpen: false,
        resizable: false,
        modal: true,
        width: '520px'
    });

    $('#tenant-revoke-invotation').dialog({
        position: "center",
        autoOpen: false,
        resizable: false,
        modal: true,
        width: '520px'
    });

    $('#tenant-add-property-popup').dialog({
        position: "center",
        autoOpen: false,
        resizable: false,
        modal: true,
        width: '520px'
    });

    $('#tenant-end-contract').dialog({
        position: "center",
        autoOpen: false,
        resizable: false,
        modal: true,
        width: '520px'
    });

    $('#contract-remove-popup').dialog({
        position: "center",
        autoOpen: false,
        resizable: false,
        modal: true,
        width: '520px'
    });

    $('#contract-duplicate-popup').dialog({
        position: "center",
        autoOpen: false,
        resizable: false,
        modal: true,
        width: '520px',
        close: function () {
        }
    });

    $('#searchFilter').linkselect("destroy");
    $('#searchFilter').linkselect({
        change: function (li, value, text) {
            ContractsViewModel.searchText('');
            if (value == 'status') {
                $('#searchSelect').show();
                $('#searchInput').hide();
            } else {
                $('#searchSelect').hide();
                $('#searchInput').show();
            }
        }
    });

    if ($('#searchColumn').val().length > 0 && $('#searchText').val().length > 0) {
        $('#searchFilter').linkselect('val', $('#searchColumn').val());
        ContractsViewModel.isSearch(true);
        ContractsViewModel.searchCollum($('#searchColumn').val());
        ContractsViewModel.searchText($('#searchText').val());
    }
    ContractsViewModel.ajaxAction();

    $('#tenant-add-property-button-cancel').click(function () {
        $('#tenant-add-property-popup').dialog('close');
        return false;
    });


    $(idProperty).linkselect('destroy');
    $(idProperty).linkselect({
        change: function (li, value, text) {
            ContractsViewModel.getUnits(value);
        }
    });

    //@TODO make it by knokout
    $('#rentjeeves_landlordbundle_invitetenantcontracttype_tenant_email').change(function () {
        $.ajax({
            url: Routing.generate('landlord_check_email'),
            type: 'POST',
            dataType: 'json',
            data: {'email': $(this).val() },
            success: function (response) {
                if (response.userExist) {
                    if (response.isTenant == false) {
                        $('.userInfo').hide();
                        $('#userExistMessageLanlord').show();
                    } else {
                        $('.userInfo').hide();
                        $('#userExistMessage').show();
                        $.each($('.userInfo').find('input'), function (index, value) {
                            var val = $.trim($(this).val());
                            if (val.length <= 0) {
                                //Fill value by wrong data for fix bug in js validation
                                //It's temp hack, @TODO fix that to group validation by js
                                $(this).val('Test');
                            }
                        });
                        $('#rentjeeves_landlordbundle_invitetenantcontracttype_tenant_phone').val(1234567890);
                    }

                    if (response.isIntegrated && response.residentId) {
                        $('#rentjeeves_landlordbundle_invitetenantcontracttype_resident_residentId').val(response.residentId);
                    }
                } else {
                    $('.userInfo').show();
                    $('.messageInfoUserAdd').hide();
                    $.each($('.userInfo').find('input'), function (index, value) {
                        var val = $.trim($(this).val());
                        if (val.length <= 0) {
                            $(this).val('');
                        }
                    });
                }
            }
        });
    });

    $('#rentjeeves_landlordbundle_invitetenantcontracttype').submit(function () {
        if ($('#userExistMessageLanlord').is(':visible')) {
            return false;
        }
        return true;
    });
});
