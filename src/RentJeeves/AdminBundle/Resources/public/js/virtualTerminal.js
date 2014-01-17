function charge(groupId) {
    var amount = parseInt($('.terminal_amount').val());
    var customData = $('.terminal_custom').val();

    if (isNaN(amount) || amount <= 0) {
        alert('Amount should be greater than 0');
        return false;
    }
    if (customData.length == 0 || customData.length > 14) {
        alert('ID4 field is required and should not contain more than 14 characters');
        return false;
    }

    if (confirm("Are you sure you want to charge $" + amount + " this group?")) {
        jQuery.ajax({
            url: Routing.generate('admin_rj_group_terminal', {'id' : groupId}),
            type: 'POST',
            dataType: 'json',
            data: {
                amount: amount,
                customData: customData
            },
            timeout: 30000, // 30 secs
            success: function(response) {
                alert(response.message);
            }
        });
    }
}
