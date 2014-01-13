var ContractsViewModel = new Contracts();
var DetailsViewModel = new Contract();

$(document).ready(function () {
    var idProperty = '#rentjeeves_landlordbundle_invitetenantcontracttype_contract_property';
    var idUnit = '#rentjeeves_landlordbundle_invitetenantcontracttype_contract_unit';

    ko.applyBindings(ContractsViewModel, $('#contracts-block').get(0));
    ko.applyBindings(DetailsViewModel, $('#contract-actions').get(0));
    $('#tenant-approve-property-popup').dialog({
        position: ["center", 200],
        autoOpen: false,
        resizable: false,
        modal: true,
        width: '520px'
    });
    $('#tenant-edit-property-popup').dialog({
        position: "center",
        autoOpen: false,
        resizable: false,
        modal: true,
        width: '520px'
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

    ContractsViewModel.ajaxAction();
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

    $('#tenant-add-property-button-cancel').click(function () {
        $('#tenant-add-property-popup').dialog('close');
        return false;
    });
    function getUnits(propertyId) {
        $(idUnit).linkselect('destroy');
        $(idUnit).html(' ');
        $(idUnit).linkselect();
        $.ajax({
            url: Routing.generate('landlord_units_list'),
            type: 'POST',
            dataType: 'json',
            data: {'property_id': propertyId},
            success: function (response) {

                if (response.units.length <= 0) {
                    return;
                }

                var html = '';
                $.each(response.units, function (index, value) {
                    var id = $(this).get(0).id;
                    var name = $(this).get(0).name;
                    var option = '<option value="' + id + '">' + name + '</option>';
                    html += option;
                });

                $(idUnit).linkselect('destroy');
                $(idUnit).html(html);
                $(idUnit).linkselect();
            }
        });
    }

    $(idProperty).linkselect('destroy');
    $(idProperty).linkselect({
        change: function (li, value, text) {
            getUnits(value);
        }
    });

    getUnits($(idProperty).linkselect('val'));

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
                                $(this).val('');
                            }
                        });
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
