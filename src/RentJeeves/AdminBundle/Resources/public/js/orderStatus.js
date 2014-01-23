function changeStatus(sel) {
    var jSel = $(sel);
    var prev = jSel.attr('prev');
    if (confirm("Are you sure you want to change the status?")) {
        jQuery.ajax({
        url: Routing.generate('admin_order_status'),
        type: 'POST',
        data: {
            status: sel.value,
            order_id: jSel.attr('order_id')
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
