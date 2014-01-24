function changeVerificationStatus(sel) {
    var jSel = $(sel);
    var prev = jSel.attr('prev');
    if (confirm("Are you sure you want to change the status?")) {
        jQuery.ajax({
            url: Routing.generate('admin_user_verification_status'),
            type: 'POST',
            data: {
                status: sel.value,
                user_id: jSel.attr('user_id')
            },
            timeout: 30000, // 30 secs
            error: function() {
                jSel.val(prev);
                jSel.attr('prev', prev);
            }
        });
    } else {
        jSel.val(prev);
        jSel.attr('prev', prev);
    }
}
