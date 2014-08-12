$( document ).ready(function() {

    var group = $($('select').get(0));
    var property = $($('select').get(1));
    var unit = $($('select').get(2));

    function choice(parent, child, route, callback)
    {
        $(".tab-content").showOverlay();
        var idSelected = child.val();
        child.html('');
        jQuery.ajax({
            url: Routing.generate(route),
            type: 'POST',
            dataType: 'json',
            data: {
                id: parent.val(),
                groupId: group.val()
            },
            error: function(jqXHR, textStatus, errorThrown) {
                window.location.reload();
            },
            success: function(data, textStatus, jqXHR) {
                callback(data, idSelected);
                $(".tab-content").hideOverlay();
            }
        });
    }

    var groupCallback = function(data, idSelected)
    {

        $.each(data, function( index, value ) {
            var option = $('<option/>');
            option.attr({ 'value': value.id }).text(value.full_address);
            if (idSelected == value.id) {
                option.attr({ 'value': 'selected' });
                isSingle = value.is_single;
            }
            property.append(option);
        });

    }

    var propertyCallback = function(data, idSelected)
    {
        $(".tab-content").showOverlay();
        $.each(data, function( index, value ) {
            var option = $('<option/>');
            option.attr({ 'value': value.id }).text(value.name);
            if (idSelected == value.id) {
                option.attr({ 'value': 'selected' })
            }
            unit.append(option);
        });
        $(".tab-content").hideOverlay();
    }

    function runGroup()
    {
        choice(
            group,
            property,
            'admin_rj_group_properties',
            groupCallback
        );
    }

    function runProperty()
    {
        choice(
            property,
            unit,
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
