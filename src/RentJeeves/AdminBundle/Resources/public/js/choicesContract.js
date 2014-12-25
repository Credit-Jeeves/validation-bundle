$( document ).ready(function() {
    console.info('hjjjj');
    var group = $($('select').get(2));
    var property = $($('select').get(3));
    var unit = $($('select').get(4));
    var holding = $($('select').get(1));

    function choice(object, route, callback)
    {
        $(".tab-content").showOverlay();
        jQuery.ajax({
            url: Routing.generate(route),
            type: 'POST',
            dataType: 'json',
            data: {
                id: object.val(),
                groupId: group.val(),
                holdingId: holding.val()
            },
            error: function(jqXHR, textStatus, errorThrown) {
                //window.location.reload();
            },
            success: function(data, textStatus, jqXHR) {
                $(".tab-content").hideOverlay();
                callback(data);
            }
        });
    }

    var groupCallback = function(data)
    {
        var propertyId = property.val();
        property.html(" ")
        $.each(data, function( index, value ) {
            var option = $('<option/>');
            option.attr({ 'value': value.id }).text(value.full_address);
            if (propertyId == value.id) {
                option.attr({ 'selected': true });
            }
            property.append(option);
        });
        runProperty()
    }

    var propertyCallback = function(data)
    {
        var unitId = unit.val();
        unit.html(" ");
        $.each(data, function( index, value ) {
            var option = $('<option/>');
            option.attr({ 'value': value.id }).text(value.name);
            if (unitId == value.id) {
                option.attr({ 'selected': true })
            }
            unit.append(option);
        });
    }

    var holdingCallback = function(data)
    {
        var groupId = group.val();
        group.html(" ");
        $.each(data, function( index, value ) {
            var option = $('<option/>');
            option.attr({ 'value': value.id }).text(value.name);
            if (groupId == value.id) {
                option.attr({ 'selected': true })
            }
            group.append(option);
        });
        runGroup();
    }

    function runGroup()
    {
        choice(
            group,
            'admin_rj_group_properties',
            groupCallback
        );
    }

    function runProperty()
    {
        choice(
            property,
            'admin_rj_group_units',
            propertyCallback
        );
    }

    function runHolding()
    {
        choice(
            holding,
            'admin_rj_holding_groups',
            holdingCallback
        )
    }

    group.change(function() {
        runGroup();
    });

    property.change(function() {
        runProperty();
    });

    holding.change(function() {
        runHolding();
    })

    //runProperty();
});
