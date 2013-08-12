$(document).ready(function(){

    function checkDeleteButton()
    {
      if($('#property-search').val() != '') {
        $('#delete').show();
      } else {
        $('#delete').hide();
      }
    }
    
    var ERROR = 'notfound';
    var markersArray = [];

    function showError(message)
    {
        return alert(message);
    }


    function search(place, map) 
    {
        var data = {'address': place.address_components, 'geometry':place.geometry, 'addGroup': 0};

        jQuery.ajax({
          url: Routing.generate('landlord_property_add'),
          type: 'POST',
          dataType: 'json',
          data: {'data': JSON.stringify(data, null)},
          error: function(jqXHR, errorThrown, textStatus) {;
          },
          success: function(data, textStatus, jqXHR) {
              if(data.hasLandlord) {
                return location.href = Routing.generate('property_add_id', {'propertyId':data.property.id });
              } else {
                return location.href = Routing.generate('tenant_invite_landlord', {'propertyId':data.property.id });
              }
          }
        });
    }


    function initialize() {
        var lat = 40;
        var lng = 100;

        var mapOptions = {
            center: new google.maps.LatLng(lat, lng),
            zoom: 15,
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
              return false;
          } 
        }
        
        $('#property-search').change(function(){
          $(this).addClass('notfound');
          checkDeleteButton();
        });

        $('#search-submit').click(function(){
            var place = autocomplete.getPlace();
            if (ERROR == $('#property-search').attr('class')) {
                return showError('Such address doesn\'t exist!');
            }

            if ('' == $('#property-search').val()) {
                return showError('Property Address empty');
            }

            if (typeof place.geometry == 'undefined') {
                return showError('Such address doesn\'t exist!');
            }

            search(place, map);
            return false;
        });

        google.maps.event.addListener(autocomplete, 'place_changed', validateAddress);
    }

    $('#formSearch').submit(function() {
      return false;
    });

    google.maps.event.addDomListener(window, 'load', initialize);

    $('#delete').click(function() {
      $('#property-search').val(' ');
      return false;
    });
});