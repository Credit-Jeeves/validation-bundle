/**
 * Author:  Alexandr Sharamko
 */
(function ( $ ) {

    $.fn.google = function( options ) {

        var ERROR = 'notfound';

        var settings = $.extend({
            // These are the defaults.
            formId: "formSearch",
            findButtonId: "property-add",
            findInputId: $(this).attr('id'),
            mapCanvasId: 'map-canvas',
            loadingSpinner: true,
            loadingSpinnerClass: 'loadingSpinner',
            autoHideLoadingSpinner: false,
            linkAddProperty: Routing.generate('landlord_property_add'),
            addPropertyCallback: function(data, textStatus, jqXHR){},
            divIdError: false
        }, options );



        function showError(message)
        {
            if (settings.divIdError === false) {
                alert(message);
            } else {
                $('#'+settings.divIdError).show();
                $('#'+settings.divIdError).html(message);
            }
            $('#'+settings.findButtonId).removeClass('grey');
            $('#'+settings.findButtonId).removeClass('disabled');
        }

        function initialize() {
            var mapOptions = {
                center: new google.maps.LatLng(38, -90),
                zoom: 4,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            var map = new google.maps.Map(
                document.getElementById(settings.mapCanvasId),
                mapOptions
            );
            var input = (document.getElementById(settings.findInputId));
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
                    ].join(' ');
                }
                infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
                infowindow.open(map, marker);
            }

            $('#'+settings.findInputId).change(function(){
                $(this).addClass('notfound');
            });

            function executeSearch(data)
            {
                if (typeof data == 'undefined') {
                    showError(Translator.get('select.from.drop.down.list'));
                    return false;
                }

                if (data.address.length < 5) {
                    showError(Translator.get('fill.full.address'));
                    return false;
                }

                if (settings.loadingSpinner) {
                    $('#'+settings.findButtonId).parent().find('.'+settings.loadingSpinnerClass).show();
                }

                jQuery.ajax({
                    url: settings.linkAddProperty,
                    type: 'POST',
                    dataType: 'json',
                    data: {'data': JSON.stringify(data, null)},
                    error: function(jqXHR, errorThrown, textStatus) {
                        location.reload();
                    },
                    success: function(data, textStatus, jqXHR) {
                        settings.addPropertyCallback(data, textStatus, jqXHR);

                        if (settings.autoHideLoadingSpinner === false) {
                            return;
                        }
                        $('#'+settings.findButtonId).removeClass('grey');
                        $('#'+settings.findButtonId).removeClass('disabled');
                        if (settings.loadingSpinner) {
                            $('#'+settings.findButtonId).parent().find('.'+settings.loadingSpinnerClass).hide();
                        }
                    }
                });
            }

            function initialCheck() {
                if (settings.divIdError !== false) {
                    $('#'+settings.divIdError).hide();
                }

                if($('#'+settings.findButtonId).hasClass('grey')) {
                    return false;
                }

                $('#'+settings.findButtonId).addClass('disabled grey');

                if ('' == $('#'+settings.findInputId).val()) {
                    showError(Translator.get('error.property.empty'));
                    return false;
                }

                if (typeof data != 'undefined') {
                    delete data;
                }

                var place = autocomplete.getPlace();
                if (typeof place != 'undefined' && typeof place.address_components != 'undefined') {
                    var data = {'address': place.address_components, 'geometry':place.geometry};
                    executeSearch(data);
                } else {
                    infowindow.close();
                    var addressText = $('#'+settings.findInputId).val();
                    //var addressText = $(".pac-container .pac-item:first").text();
                    var geocoder = new google.maps.Geocoder();
                    geocoder.geocode({"address":addressText }, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            var lat = results[0].geometry.location.lat(),
                                lng = results[0].geometry.location.lng(),
                                placeName = results[0].address_components[0].long_name,
                                latlng = new google.maps.LatLng(lat, lng);

                            marker.setPosition(latlng);
                            infowindow.setContent(placeName);
                            infowindow.open(map, marker);

                            $("#"+settings.findInputId).val(addressText);
                            var data = {'address': results[0].address_components, 'geometry':results[0].geometry};
                            executeSearch(data);
                        } else {
                            showError(Translator.get('select.from.drop.down.list'));
                        }
                    });
                }
            }

            $('#'+settings.findInputId).keypress(function(e) {
                if(e.which == 13) {
                    initialCheck();
                }
            });

            $('#'+settings.findButtonId).click(function(){
                initialCheck();
            });

            google.maps.event.addListener(autocomplete, 'place_changed', validateAddress);
        }



        google.maps.event.addDomListener(window, 'load', initialize);

        $('#'+settings.formId).submit(function() {
            return false;
        });
    };

}( jQuery ));