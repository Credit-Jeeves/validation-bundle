$(document).ready(function() {
    $('#landlord-verify-email').click(function()
        {
            alert(155);
            $.ajax({
                url: Routing.generate('landlord_resend_verification'),
                type: 'POST',
                dataType: 'json',
                data: {}
            });
            $(this).hide();
        }
    );
});
