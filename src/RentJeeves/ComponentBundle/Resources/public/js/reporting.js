$(document).ready(function(){
    $('.reporting-action').click(function(){
        var id = this.id.split('-')[1];
        $('#reporting-start-' + id).dialog('open');
    });
    $('.close-start').click(function(){
        var id = this.id.split('-')[1];
        $('#reporting-start-' + id).dialog('close');
    });
    $('.start-reporting').click(function(){
        var id = this.id.split('-')[2];
        var experianFlag = $('#reportExperian-' + id).is(':checked');
        var tuFlag = $('#reportTU-' + id).is(':checked');
        $.ajax({
            url: Routing.generate('tenant_contract_reporting'),
            type: 'POST',
            dataType: 'json',
            data: {
                contractId : id,
                experianReporting: experianFlag,
                tuReporting: tuFlag
            },
            success: function() {
                $('#reporting-start').dialog('close');
            }
        });
    });
    $('.reporting-start').dialog({
        autoOpen: false,
        resizable: false,
        modal: true,
        width:'520px'
    });
});
