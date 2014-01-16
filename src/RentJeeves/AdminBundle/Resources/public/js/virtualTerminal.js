function charge(groupId) {
    var amount = parseInt($('.charge_amount').val());

    if (isNaN(amount) || amount <= 0) {
        alert('Amount should be greater than 0');
        return false;
    }

    if (confirm("Are you sure you want to charge $" + amount + " this group?")) {
        jQuery.ajax({
            url: Routing.generate('admin_rj_group_terminal', {'id' : groupId}),
            type: 'POST',
            dataType: 'json',
            data: {
                amount: amount
            },
            timeout: 30000, // 30 secs
            error: function(response) {
                alert('error is ' + response.message);
            },
            success: function(response) {
                alert('success ' + response.message);
            }
        });
    }
}
