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
          input.className = 'notfound';
          return;
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
      if('notfound' == $('#property-search').attr('class')) {
            alert('Such address doesn\'t exist!');
            return false;
        }
        if ('' == $('#property-search').val()) {
          alert('Property Address empty');
          return false;
        }
        var place = autocomplete.getPlace();
        var data = {'address': place.address_components, 'geometry':place.geometry};
        jQuery.ajax({
          url: Routing.generate('landlord_property_add'),
          type: 'POST',
          dataType: 'json',
          data: {'data': JSON.stringify(data, null)},
          error: function(jqXHR, errorThrown, textStatus) {
              //jQuery('#simulation-container').hideOverlay();
          },
          success: function(data, textStatus, jqXHR) {
            
          }
      });
        
//        console.log(JSON.stringify(autocomplete.getPlace()));
//        alert($('#property-search').val());
    });
    google.maps.event.addListener(autocomplete, 'place_changed', validateAddress);
}
google.maps.event.addDomListener(window, 'load', initialize);
