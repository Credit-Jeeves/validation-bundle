$(function() {
    $('#landlord-verify-email').click(function()
        {
            var self = this;
            $.ajax({
                url: Routing.generate('landlord_resend_verification'),
                type: 'POST',
                dataType: 'json',
                data: {},
                success: function() {
                    $(self).hide();
                    $('#landlord-verify-resent').show();
                }
            });

            return false;
        }
    );
});
