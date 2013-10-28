$(document).ready(function(){
    
    function showError(message)
    {
        return $('#errorForm').html(message);
    }

    function clearError()
    {
        return $('#errorForm').html(' ');
    }

    function markAsNotValid()
    {
        $('#addUnit').addClass('grey');
        $('#addProperty').addClass('grey');
    }

    var ERROR = 'notfound';

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

    $('#delete').click(function(){
        $('#property-search').val(' ');
        markAsNotValid();
        $(this).hide();
        return false;
    });



    function initialize() {
        var lat = 0.0;
        var lng = 0.0;

        var mapOptions = {
            center: new google.maps.LatLng(lat, lng),
            zoom: 15,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(
            document.getElementById('search-result-map'),
            mapOptions
        );
        var input = (document.getElementById('property-search'));
        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo('bounds', map);
        var infowindow = new google.maps.InfoWindow();
        var marker = new google.maps.Marker({
                map: map
        });

        function validateAddress()
        {
            clearError();
            if($('#property-search').val() != '') {
                $('#delete').show();
            } else {
                $('#delete').hide();
            }
            infowindow.close();
            marker.setVisible(false);
            input.className = '';
            
            markAsNotValid();

            var place = autocomplete.getPlace();
            //Inform the user that the place was not found and return.
            if (!place.geometry) {
                input.className = ERROR;
            }

            if (ERROR == $('#property-search').attr('class')) {
                return showError('Such address doesn\'t exist!');
            }

            if ('' == $('#property-search').val()) {
                return showError('Property Address empty');
            }

            if (typeof place.geometry == 'undefined') {
                return showError('Such address doesn\'t exist!');
            }

            $('#addUnit').removeClass('grey');
        }
        
        $('#property-search').change(function(){
          $(this).addClass('notfound');
          markAsNotValid();
          if($(this).val() != '') {
            $('#delete').show();
          } else {
            $('#delete').hide();
          }
        });

        google.maps.event.addListener(autocomplete, 'place_changed', validateAddress);

        function getUnits() {
            var unitsList = new Array();
            $.each($('.unitsListNames').find('.unit-name'), function(index, value) {
               unitsList.push({'name': $(this).val(), 'id': ''});
            });

            return unitsList;
        }


        function addProperty() 
        {
            var place = autocomplete.getPlace();
            var data = {'address': place.address_components, 'geometry':place.geometry, 'addGroup': 0};
            jQuery.ajax({
                url: Routing.generate('landlord_property_add'),
                type: 'POST',
                dataType: 'json',
                async: false,
                data: {'data': JSON.stringify(data, null)},
                error: function(jqXHR, errorThrown, textStatus) {;
                },
                success: function(data, textStatus, jqXHR) {
                    var propertyId = data.property.id
                    if(propertyId) {
                        $('#LandlordAddressType_property').val(propertyId);
                        return true;
                    }

                    return false;
                }
            });
        }

        $('#LandlordAddressType').submit(function() {
            $('#submitForm').hide();
            $('.loader').show();
            if (ERROR == $('#property-search').attr('class')) {
                return true;
            }
            addProperty();
            return true;
        });
    }

    google.maps.event.addDomListener(window, 'load', initialize);
});