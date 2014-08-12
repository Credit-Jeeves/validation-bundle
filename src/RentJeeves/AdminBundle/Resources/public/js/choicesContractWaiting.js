$( document ).ready(function() {

    var group = $($('select').get(0));
    var property = $($('select').get(1));
    var unit = $($('select').get(2));

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
                window.location.reload();
            },
            success: function(data, textStatus, jqXHR) {
                callback(data);
                $(".tab-content").hideOverlay();
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
                isSingle = value.is_single;
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
            'admin_rj_group_unit',
            propertyCallback
        );
    }

    group.change(function() {
        runGroup();
    });

    property.change(function() {
        runProperty();
    });

    runGroup();
});
