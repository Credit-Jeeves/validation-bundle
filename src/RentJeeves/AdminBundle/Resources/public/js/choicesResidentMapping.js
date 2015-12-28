$( document ).ready(function() {

    var holding = $('input[id$=_holding]');
    var tenant = $($('select').get(0));

    function choice(route, callback)
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
                //window.location.reload();
            },
            success: function(data, textStatus, jqXHR) {
                callback(data);
                $(".tab-content").hideOverlay();
            }
        });
    }

    var tenantCallback = function(data)
    {
        console.info(data);
        var tenantId = tenant.val();
        tenant.html(" ")
        $.each(data, function( index, value ) {
            var option = $('<option/>');
            option.attr({ 'value': value.id }).text(value.email);
            if (tenantId == value.id) {
                option.attr({ 'selected': true });
            }
            tenant.append(option);
        });
    }

    function runTenant()
    {
        choice(
            'admin_rj_residentMapping_tenants',
            tenantCallback
        );
    }

    holding.change(function() {
        runTenant();
    });

    runTenant();
});
