jQuery(document).ready(function ($) {

    // Sonata's batch checkbox checker
    $('#list_batch_checkbox').click(function () {
        $(this).closest('table').find("td input[type='checkbox']").attr('checked', $(this).is(':checked')).parent().parent().toggleClass('sonata-ba-list-row-selected', $(this).is(':checked'));
    });
    $("td.sonata-ba-list-field-batch input[type='checkbox']").change(function () {
        $(this).parent().parent().toggleClass('sonata-ba-list-row-selected', $(this).is(':checked'));
    });

    $('#form_send_notification').submit(function () {
        var formData = $('#form_send_notification').serializeArray();
        var url = $('#form_send_notification').attr('action');
        var month = $('#rental_report_month_month').val();
        $('#form_send_notification').showOverlay();
        formData.push({
            'name': 'month',
            'value': month
        });

        $.ajax({
            url: url,
            type: 'POST',
            timeout: 60000, // 30 secs
            dataType: 'json',
            data: jQuery.param(formData, false),
            error: function() {
                $('#form_send_notification').hideOverlay();
                alert(Translator.trans('admin.late_report.error'));
            },
            success: function() {
                $('#form_send_notification').hideOverlay();
                alert(Translator.trans('admin.late_report.emails_sent'));
            }
        });

        return false;
    });
});
