function changeDisputeCode(sel) {
    var jSel = $(sel);
    var prev = jSel.attr('prev');
    if (confirm("Are you sure you want to change the dispute code?")) {
        jQuery.ajax({
            url: Routing.generate('admin_contract_dispute_code'),
            type: 'POST',
            data: {
                dispute_code: sel.value,
                contract_id: jSel.attr('contract_id')
            },
            timeout: 30000,
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
