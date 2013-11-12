$(document).ready(function(){
    
    function markAsValid()
    {
        $('#addUnit').removeClass('grey');
        $('#addUnit').removeClass('disabled');
        $('#addProperty').removeClass('grey');
        $('#addProperty').removeClass('disabled');
    }

    function markAsNotValid()
    {
        $('#addUnit').addClass('grey');
        $('#addUnit').addClass('disabled');
        $('#addProperty').addClass('grey');
        $('#addProperty').addClass('disabled');
    }


    $('#addUnit').click(function(){
        if($(this).hasClass('grey')) {
            return false;
        }

        var i = parseInt($('#numberOfUnit').val());
         var input = '<input type="text" value="" class="unit-name" name="unit-name[]">';
        for(k = 1; k <= i; k++) {
            $('.unitsListNames').append(input);
        }

        return false;
    });

    var google = $('#property-search').google({
        formId: null,
        findButtonId: "search-submit",
        findInputId: "property-search",
        mapCanvasId: 'search-result-map',
        loadingSpinner: true,
        loadingSpinnerClass: 'loadingSpinner',
        autoHideLoadingSpinner: true,
        addPropertyCallbackNotValid: function(jqXHR, errorThrown, textStatus)
        {
            markAsNotValid();
        },
        clearSearchCallback: function(isEmpty)
        {
            if (isEmpty) {
                markAsNotValid();
            }
        },
        markers: false,
        clearSearchId: 'delete',
        classError: 'errorsGoogleSearch',
        addPropertyCallback: function(data, textStatus, jqXHR)
        {
            markAsValid();
        },
        changeSearch: function(){
            markAsNotValid();
        }
    });

    function getUnits() {
        var unitsList = new Array();
        $.each($('.unitsListNames').find('.unit-name'), function(index, value) {
            unitsList.push({'name': $(this).val(), 'id': ''});
        });

        return unitsList;
    }

    function saveUnitList(propertyId)
    {
        var units = getUnits();

        if(units.length === 0) {
            return location.href = Routing.generate('landlord_properties');
        }

        jQuery.ajax({
            url: Routing.generate('landlord_units_save'),
            type: 'POST',
            dataType: 'json',
            data: {'units': units, 'property_id': propertyId },
            error: function(jqXHR, errorThrown, textStatus) {
                google.showError(Translator.get('fill.full.address'));
            },
            success: function(data, textStatus, jqXHR) {
                return location.href = Routing.generate('landlord_properties');
            }
        });
    }

            jQuery.ajax({
                url: Routing.generate('landlord_units_save'),
                type: 'POST',
                dataType: 'json',
                data: {'units': units, 'property_id': propertyId },
                error: function(jqXHR, errorThrown, textStatus) {;
                },
                success: function(data, textStatus, jqXHR) {
                    return location.href = Routing.generate('landlord_properties');
                }
            });
        

        function addProperty() 
        {
            var place = autocomplete.getPlace();
            var data = {'address': place.address_components, 'geometry':place.geometry};
            jQuery.ajax({
                url: Routing.generate('landlord_property_add'),
                type: 'POST',
                dataType: 'json',
                data: {'data': JSON.stringify(data, null)},
                error: function(jqXHR, errorThrown, textStatus) {;
                },
                success: function(data, textStatus, jqXHR) {
                    var propertyId = data.property.id;
                    if(propertyId) {
                        return saveUnitList(propertyId);
                    }

                    showError('Something wrong, we can\'t save property');
                

                google.showError(Translator.get('fill.full.address'));
            }
        });
    }

    $('#addProperty').click(function(){
        if($(this).hasClass('grey')) {
            return false;
        }
        $('.loader').parent().find('span').hide();
        $('.loader').show();
        addProperty();
        return false;
    });
});