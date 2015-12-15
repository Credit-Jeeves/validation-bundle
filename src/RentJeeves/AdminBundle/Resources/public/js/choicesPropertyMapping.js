$( document ).ready(function() {
    var holding = $('input[id$=_holding]');
    var property = $($('select').get(1));
    var route = 'admin_property_mapping';

    function load(route, callback)
    {
        $(".tab-content").showOverlay();
        jQuery.ajax({
            url: Routing.generate(route),
            type: 'POST',
            dataType: 'json',
            data: {
                holdingId: holding.val()
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.info('An error has occurred while processing your request: ' + errorThrown);
            },
            success: function(data, textStatus, jqXHR) {
                callback(data);
                $(".tab-content").hideOverlay();
            }
        });
    }

    var callback = function(data)
    {
        var elementId = property.val();
        property.html(" ")
        $.each(data, function(index, value) {
            var option = $('<option/>');
            option.attr({ 'value': value.id }).text(value.full_address);
            if (elementId == value.id) {
                option.attr({ 'selected': true });
            }
            property.append(option);
        });
    }

    function run()
    {
        load(route, callback);
    }

    holding.change(function() {
        run();
    });

    run();
});
