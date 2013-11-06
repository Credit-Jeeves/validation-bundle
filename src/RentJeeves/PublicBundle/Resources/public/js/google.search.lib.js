/**
 * Author:  Alexandr Sharamko
 */
(function ( $ ) {

    $.fn.google = function( options ) {

        var ERROR = 'notfound';
        var self = this;
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
            addPropertyCallback: function(data, textStatus, jqXHR, self){},
            markers: false,
            divIdError: false,
            defaultLat: null,
            defaultLong: null,
            clearSearchId: null
        }, options );


        if (settings.clearSearchId != null) {
            $('#'+settings.findInputId).keyup(function(){
                if ($(this).val().length > 0) {
                    $('#'+settings.clearSearchId).show();
                } else {
                    $('#'+settings.clearSearchId).hide();
                }
            });

            $('#'+settings.clearSearchId).click(function() {
                $('#'+settings.findInputId).val(' ');
                return false;
            });
        }
        this.deleteOverlays = function()
        {
            if (self.markersArray) {
                for (i in self.markersArray) {
                    self.markersArray[i].setMap(null);
                }
            }
        }

        this.getHtmlPopap = function(title, content)
        {
            return  '<div id="content">'+
                '<div id="siteNotice">'+
                '</div>'+
                '<h1 id="firstHeading" class="firstHeading">'+title+'</h1>'+
                '<div id="bodyContent" style="width:150px;">'+content +
                '<p></div>'+
                '</div>';
        }

        self.markersArray = [];

        this.rentaPiontShadow = new google.maps.MarkerImage('/bundles/rjpublic/images/ill-renta-point_shadow.png',
            new google.maps.Size(38,54),
            new google.maps.Point(0,0),
            new google.maps.Point(19, 41)
        );

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
            if (settings.defaultLat != null && settings.defaultLong != null) {
                var mapOptions = {
                    center: new google.maps.LatLng(settings.defaultLat, settings.defaultLong),
                    zoom: 14,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };
            } else {
                 var mapOptions = {
                    center: new google.maps.LatLng(38, -90),
                    zoom: 4,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                 };
            }
            self.map = new google.maps.Map(
                document.getElementById(settings.mapCanvasId),
                mapOptions
            );
            var input = (document.getElementById(settings.findInputId));
            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo('bounds', self.map);
            var infowindow = new google.maps.InfoWindow();
            //setup markers
            if (settings.markers) {
                $.each($('.addressText'), function(index, value) {
                    var lat = $(this).find('.lat').val();
                    var lng = $(this).find('.lng').val();
                    var addressSelect = $(this).find('.addressSelect').val();
                    var number = $(this).attr('number');
                    var myLatlng = new google.maps.LatLng(lat,lng);
                    var rentaPoint = new google.maps.MarkerImage('/bundles/rjpublic/images/ill-renta-point_'+number+'.png',
                        new google.maps.Size(26,42),
                        new google.maps.Point(0,0),
                        new google.maps.Point(13,42)
                    );

                    var contentString = self.getHtmlPopap(
                        $(this).find('.titleAddress').html(),
                        $(this).find('.contentAddress').html()
                    );

                    var infowindow = new google.maps.InfoWindow({
                        content: contentString
                    });

                    var marker = new google.maps.Marker({
                        position: myLatlng,
                        map: self.map,
                        title: addressSelect,
                        icon: rentaPoint,
                        shadow: self.rentaPiontShadow
                    });

                    self.markersArray[number] = marker;

                    google.maps.event.addListener(marker, 'click', function() {
                        infowindow.open(self.map, self.markersArray[number]);
                    });

                });
            }
            //end setup markers
            var marker = new google.maps.Marker({
                map: self.map
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
                    self.map.fitBounds(place.geometry.viewport);
                } else {
                    self.map.setCenter(place.geometry.location);
                    self.map.setZoom(15);  // Why 17? Because it looks good.
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
                infowindow.open(self.map, marker);
            }

            $('#'+settings.findInputId).change(function(){
                $(this).addClass('notfound');
            });

            function afterAddProperty()
            {
                if (settings.autoHideLoadingSpinner === false) {
                    return;
                }
                $('#'+settings.findButtonId).removeClass('grey');
                $('#'+settings.findButtonId).removeClass('disabled');
                if (settings.loadingSpinner) {
                    $('#'+settings.findButtonId).parent().find('.'+settings.loadingSpinnerClass).hide();
                }
            }

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
                        afterAddProperty();
                        showError(Translator.get('fill.full.address'));
                        return false;
                    },
                    success: function(data, textStatus, jqXHR) {
                        settings.addPropertyCallback.call(self, data, textStatus, jqXHR);
                        afterAddProperty();
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

                self.place = autocomplete.getPlace();
                if (typeof self.place != 'undefined' && typeof self.place.address_components != 'undefined') {
                    var data = {'address': self.place.address_components, 'geometry': self.place.geometry};
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
                            infowindow.open(self.map, marker);

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