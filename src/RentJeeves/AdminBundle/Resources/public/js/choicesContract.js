$( document ).ready(function() {
    var group = $($('select').get(2));
    var property = $($('select').get(3));
    var unit = $($('select').get(4));

    function choice(object, route, callback)
    {
        $(".tab-content").showOverlay();
        jQuery.ajax({
            url: Routing.generate(route),
            type: 'POST',
            dataType: 'json',
            data: {
                id: object.val(),
                groupId: group.val()
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
    };

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
    };

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

    group.change(function() {
        runGroup();
    });

    property.change(function() {
        runProperty();
    });
});
