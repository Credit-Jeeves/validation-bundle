$(function() {
    $('#verify-email').click(function()
        {
            var self = this;
            var userType = $(this).data('userType');
            $.ajax({
                url: Routing.generate(userType + '_resend_verification'),
                type: 'POST',
                dataType: 'json',
                data: {},
                success: function() {
                    $(self).hide();
                    $('#verify-resent').show();
                }
            });

            return false;
        }
    );
});
