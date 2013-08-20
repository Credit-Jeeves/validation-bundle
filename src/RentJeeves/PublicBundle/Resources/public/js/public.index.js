$(document).ready(function(){

    var ERROR = 'notfound';

    function showError(message)
    {
        alert(message);
    }

    function initialize() {
        var mapOptions = {
            center: new google.maps.LatLng(38, -90),
            zoom: 4,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(
            document.getElementById('map-canvas'),
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
          infowindow.close();
          marker.setVisible(false);
          input.className = '';
          var place = autocomplete.getPlace();
          //Inform the user that the place was not found and return.
          if (!place.geometry) {
              input.className = ERROR;
              return;
          } else {
              input.className = '';
          }
          //If the place has a geometry, then present it on a map.
          if (place.geometry.viewport) {
              map.fitBounds(place.geometry.viewport);
          } else {
              map.setCenter(place.geometry.location);
              map.setZoom(15);  // Why 17? Because it looks good.
          }
          marker.setIcon(/** @type {google.maps.Icon} */({
              url: place.icon,
              size: new google.maps.Size(71, 71),
              origin: new google.maps.Point(0, 0),
              anchor: new google.maps.Point(17, 34),
              scaledSize: new google.maps.Size(35, 35)
          }));
          marker.setPosition(place.geometry.location);
          marker.setVisible(true);
          var address = '';
          if (place.address_components) {
              address = [
                  (place.address_components[0] && place.address_components[0].short_name || ''),
                  (place.address_components[1] && place.address_components[1].short_name || '')
        //        (place.address_components[2] && place.address_components[2].short_name || '')
              ].join(' ');
          }
          infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
          infowindow.open(map, marker);
        }

        $('#property-search').change(function(){
          $(this).addClass('notfound');
        });

        $('#property-add').click(function(){
            if(ERROR == $('#property-search').attr('class')) {
                showError('Such address doesn\'t exist!');
                return false;
            }
            if ('' == $('#property-search').val()) {
              showError('Property Address empty');
              return false;
            }
            var place = autocomplete.getPlace();
            if (typeof place == 'undefined') {
                showError('Such address doesn\'t exist!');
                return false;
            }
            var data = {'address': place.address_components, 'geometry':place.geometry};
            jQuery.ajax({
              url: Routing.generate('landlord_property_add'),
              type: 'POST',
              dataType: 'json',
              data: {'data': JSON.stringify(data, null)},
              error: function(jqXHR, errorThrown, textStatus) {
                //location.href = Routing.generate('landlord_properties');
              },
              success: function(data, textStatus, jqXHR) {
                var isInIFrame = (window.location != window.parent.location);
                var location = Routing.generate('iframe_search_check', {'propertyId':data.property.id});
                console.info(isInIFrame);
                if (isInIFrame == true) {
                    // iframe
                    window.parent.location.href = location;
                } else {
                    // no iframe
                    window.location.href = location;
                }
              }
            });
        });

        google.maps.event.addListener(autocomplete, 'place_changed', validateAddress);
    }

    google.maps.event.addDomListener(window, 'load', initialize);

    $('#formSearch').submit(function() {
      return false;
    });
});