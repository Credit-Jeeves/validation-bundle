$(document).ready(function(){
    
    function markAsValid()
    {
        $('#addUnit').removeClass('grey');
        $('#addProperty').removeClass('grey');
    }

    function markAsNotValid()
    {
        $('#addUnit').addClass('grey');
        $('#addProperty').addClass('grey');
    }

    $('#addUnit').click(function(){
        if($(this).hasClass('grey')) {
            return false;
        }
        var unitCount = $('#unitCount').val();
        var i = parseInt($('#numberOfUnit').val());
        for(k = 1; k <= i; k++) {
            var input = '<input type="text" value="" class="unit-name" name="LandlordAddressType[units]['+unitCount+']">';
            $('.unitsListNames').append(input);
            unitCount++;
        }
        $('#unitCount').val(unitCount);
        return false;
    });

    $('#property-search').google({
        autoHideLoadingSpinner: true,
        mapCanvasId: "search-result-map",
        formId: "formSearch",
        clearSearchId: 'delete',
        classError: "errorsGoogleSearch",
        loadingSpinner: true,
        loadingSpinnerClass: 'loadingSpinner',
        findButtonId: 'search-submit',
        clearSearchCallback: function(isEmpty){
            if (isEmpty) {
                markAsNotValid();
            }
        },
        addPropertyCallbackNotValid: function(jqXHR, errorThrown, textStatus) {
            $('#LandlordAddressType_property').val('');
            markAsNotValid();
        },
        addPropertyCallback: function(data, textStatus, jqXHR){
            $('#LandlordAddressType_property').val('');
            var propertyId = data.property.id
            if(propertyId) {
                $('#LandlordAddressType_property').val(propertyId);
                markAsValid();
                return true;
            }
            markAsNotValid();
            return false;
        }
    });

    if($('.propertyId').val().length > 0) {
        markAsValid();
    }

    if ($('.single-property-checkbox input[type=checkbox]').is(':checked') === true) {
        $('#property-units').hide();
    }

    $('.single-property-checkbox input[type=checkbox]').click(function(){
        if ($('#property-units').is(":visible") === true) {
            $('#property-units').hide();
            $('.unit-name').remove();
            $('#numberOfUnit').val('')
            $('#unitCount').val(0)
        } else {
            $('#property-units').show();
        }
    });
});
