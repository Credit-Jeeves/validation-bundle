$(function() {
    if ($('#base_order_report_type_type').length == 0) {
        return;
    }

    $('.calendar').datepicker({
        showOn: "both",
        buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
        format:'m/d/Y',
        starts: 1,
        position: 'r'
    });

    function processFieldsFormReport(type)
    {
        if(type == 'xml') {
            $('.int').parent().parent().show();
            $('.type-date').css({'margin-top':'-175px'});
        } else {
            $('.int').parent().parent().hide();
            $('.type-date').css({'margin-top':'-70px'});
        }
    }

    $('#base_order_report_type_type').change(function(){
        $('.helpContainer>a').hide();
        $('.'+$(this).val()).show();
        processFieldsFormReport($(this).val());
    });

    processFieldsFormReport($('#base_order_report_type_type option:selected').val());
});
