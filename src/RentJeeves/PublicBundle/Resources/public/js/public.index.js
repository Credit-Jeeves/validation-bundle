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
          console.info('property search changed');
          $(this).addClass('notfound');
        });

/*        $("#property-search").focusin(function () {
            $(document).keypress(function (e) {
                if (e.which == 13) {
                    infowindow.close();
                    var firstResult = $(".pac-container .pac-item:first").text();
                    var geocoder = new google.maps.Geocoder();
                    geocoder.geocode({"address":firstResult }, function(results, status) {
                        console.info(results);
                        if (status == google.maps.GeocoderStatus.OK) {
                            var lat = results[0].geometry.location.lat(),
                                lng = results[0].geometry.location.lng(),
                                placeName = results[0].address_components[0].long_name,
                                latlng = new google.maps.LatLng(lat, lng);

                            marker.setPosition(latlng);
                            infowindow.setContent(placeName);
                            infowindow.open(map, marker);
                            $("#property-search").val(firstResult);
                        }
                    });
                }
            });
        });*/

        function executeSearch(data)
        {
            console.info('Туц');
            console.info(data);
            if (typeof data == 'undefined') {
                showError(Translator.get('select.from.drop.down.list'));
                //console.info('3');
                return false;
            }


            if($('#property-add').hasClass('grey')) {
                return false;
            }

            $('#property-add').addClass('disabled grey');
            $('#property-add').find('.loadingSpinner').show();

            //console.info(place);
            //console.info(data);
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

                    if (data.isLogin && data.isLandlord) {
                        var location = Routing.generate('landlord_properties');
                    }

                    if (isInIFrame == true) {
                        // iframe
                        //window.parent.location.href = location;
                    } else {
                        // no iframe
                        //window.location.href = location;
                    }
                }
            });
        }

        $('#property-add').click(function(){

/*            if(ERROR == $('#property-search').attr('class')) {
                showError(Translator.get('select.from.drop.down.list'));
                console.info('1');
                return false;
            }*/

            if ('' == $('#property-search').val()) {
                showError(Translator.get('error.property.empty'));
                return false;
            }

            if (typeof data != 'undefined') {
                delete data;
            }

            var place = autocomplete.getPlace();
            if (typeof place != 'undefined') {
                var data = {'address': place.address_components, 'geometry':place.geometry};
                executeSearch(data);
            } else {
                infowindow.close();
                var firstResult = $(".pac-container .pac-item:first").text();
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({"address":firstResult }, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        var lat = results[0].geometry.location.lat(),
                            lng = results[0].geometry.location.lng(),
                            placeName = results[0].address_components[0].long_name,
                            latlng = new google.maps.LatLng(lat, lng);

                        marker.setPosition(latlng);
                        infowindow.setContent(placeName);
                        infowindow.open(map, marker);
                        $("#property-search").val(firstResult);
                        var data = {'address': results[0].address_components, 'geometry':results[0].geometry};
                        executeSearch(data);
                    } else {
                        showError(Translator.get('select.from.drop.down.list'));
                    }
                });
            }

        });

        google.maps.event.addListener(autocomplete, 'place_changed', validateAddress);
    }

    google.maps.event.addDomListener(window, 'load', initialize);

    $('#formSearch').submit(function() {
      return false;
    });
});