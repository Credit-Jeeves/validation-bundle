$(function() {
    $('#landlord-verify-email').click(function()
        {
            $.ajax({
                url: Routing.generate('landlord_resend_verification'),
                type: 'POST',
                dataType: 'json',
                data: {},
                success: function() {
                    $(this).hide();
                    $('#landlord-verify-resent').show();
                }
            });

            return false;
        }
    );
});
