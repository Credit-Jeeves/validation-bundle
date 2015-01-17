$( document ).ready(function() {

    var button =$('<button id="test" class="btn" />');
    button.html(Translator.trans('common.test.yardi_setting'))
    button.css({"width": "105px"});
    $(".form-actions").append(button.get(0));

    $(".form-actions").on("click", "#test", function() {
        $(".tab-content").showOverlay();
        $('.alert').remove()
        jQuery.ajax({
            url: Routing.generate('admin_check_yardi_settings'),
            type: 'POST',
            dataType: 'json',
            data:$('form').serialize(),
            error: function(jqXHR, textStatus, errorThrown) {
                $(".tab-content").hideOverlay();
                window.location.reload();
            },
            success: function(data, textStatus, jqXHR) {
                $(".tab-content").hideOverlay();
                var message = $("<div />");
                message.html(data.message);
                if (data.status === 'ok') {
                    message.attr('class', 'alert alert-success');
                } else {
                    message.attr('class', 'alert alert-error');
                }

                var data = $('.sonata-bc>.container-fluid').html();
                $('.sonata-bc>.container-fluid').prepend(message.get(0));
            }
        });

        return false;
    });
});