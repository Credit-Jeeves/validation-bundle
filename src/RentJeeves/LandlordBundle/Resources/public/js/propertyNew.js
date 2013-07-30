$(document).ready(function(){
    
    function showError(message)
    {
        return alert(message);
    }

    function markAsNotValid()
    {
        $('#addUnit').addClass('greyButton');
        $('#addProperty').addClass('greyButton');
    }

    var ERROR = 'notfound';

    $('#addUnit').click(function(){
        if(!$(this).hasClass('greyButton')) {
            return false;
        }

        var i = parseInt($('#numberOfUnit').val());
         var input = '<input type="text" value="" class="unit-name" name="unit-name[]">';
        for(k = 1; k < i; k++) {
            $('.unitsList').append(input);
        }

        return false;
    });

    $('#delete').click(function(){
        $('#property-search').val(' ');
        markAsNotValid();
        $(this).hide();
    });

    $('#addProperty').click(function(){
        if(!$(this).hasClass('greyButton')) {
            return false;
        }
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

        function validateAddress(){
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

            $('#addUnit').removeClass('greyButton');
            $('#addProperty').removeClass('greyButton');
        }
        
        $('#property-search').change(function(){
          $(this).addClass('notfound');
          if($(this).val() != '') {
            $('#delete').show();
          } else {
            $('#delete').hide();
            markAsNotValid();
          }
        });

        google.maps.event.addListener(autocomplete, 'place_changed', validateAddress);
    }

    google.maps.event.addDomListener(window, 'load', initialize);
});