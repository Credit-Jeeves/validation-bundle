/**
 * Author:  Alexandr Sharamko
 */
(function ($, google) {

    $.fn.google = function (options) {

        var self = this;
        self.isValid = false;

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
            addPropertyCallback: function (data, textStatus, jqXHR) {
            },
            addPropertyCallbackNotValid: function (jqXHR, errorThrown, textStatus) {
            },
            clearSearchCallback: function (isEmpty) {
            },
            changeSearch: function () {
            },
            markers: false,
            divIdError: false,
            classError: false,
            defaultLat: null,
            defaultLong: null,
            clearSearchId: null,
            clearSearchClass: null,
            moveToLocationClass: 'moveToLocation'
        }, options);


        if (settings.clearSearchId != null || settings.clearSearchClass) {

            if (settings.clearSearchId != null) {
                var close = $('#' + settings.clearSearchId);
            } else {
                var close = $('#' + settings.findInputId).parent().parent().find('.' + settings.clearSearchClass);
            }

            $('#' + settings.findInputId).keyup(function () {
                if ($(this).val().length > 0) {
                    settings.clearSearchCallback(false);
                    close.show();
                } else {
                    settings.clearSearchCallback(true);
                    close.hide();
                }
            });

            close.click(function () {
                $('#' + settings.findInputId).val(' ');
                settings.clearSearchCallback(true);
                close.hide();
                return false;
            });

            if ($('#' + settings.findInputId).val().length > 0) {
                settings.clearSearchCallback(false);
                close.show();
            } else {
                settings.clearSearchCallback(true);
                close.hide();
            }
        }

        this.deleteOverlays = function () {
            if (self.markersArray) {
                for (i in self.markersArray) {
                    self.markersArray[i].setMap(null);
                }
            }
        }

        this.getHtmlPopap = function (title, content) {
            var templateBody = [
                {
                    'title': title,
                    'content': content
                }
            ];
            var html = $('#htmlPopap').tmpl(templateBody).html();
            return html;
        }


        self.place = '';
        self.markersArray = [];

        this.rentaPiontShadow = new google.maps.MarkerImage('/bundles/rjpublic/images/ill-renta-point_shadow.png',
            new google.maps.Size(38, 54),
            new google.maps.Point(0, 0),
            new google.maps.Point(19, 41)
        );

        self.showError = function (message) {
            self.isValid = false;
            if (settings.divIdError === false && settings.classError === false) {
                alert(message);
            } else {
                if (settings.divIdError) {
                    $('#' + settings.divIdError).show();
                    $('#' + settings.divIdError).html(message);
                } else {
                    $('.' + settings.classError).show();
                    $('.' + settings.classError).html(message);
                }
            }

            if (settings.loadingSpinner) {
                $('#' + settings.findButtonId).parent().find('.' + settings.loadingSpinnerClass).hide();
            }

            $('#' + settings.findButtonId).removeClass('grey');
            $('#' + settings.findButtonId).removeClass('disabled');
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
                    center: new google.maps.LatLng(39, -97),
                    zoom: 4,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };
            }
            self.map = new google.maps.Map(
                document.getElementById(settings.mapCanvasId),
                mapOptions
            );
            var input = (document.getElementById(settings.findInputId));
            self.autocomplete = new google.maps.places.Autocomplete(input);
            self.autocomplete.bindTo('bounds', self.map);
            self.infowindow = new google.maps.InfoWindow();
            //setup markers
            if (settings.markers) {
                $.each($('.addressText'), function () {
                    var lat = $(this).find('.lat').val();
                    var lng = $(this).find('.lng').val();
                    var addressSelect = $(this).find('.addressSelect').val();
                    var number = $(this).attr('number');
                    var myLatlng = new google.maps.LatLng(lat, lng);
                    var rentaPoint = new google.maps.MarkerImage('/bundles/rjpublic/images/ill-renta-point_' + number + '.png',
                        new google.maps.Size(26, 42),
                        new google.maps.Point(0, 0),
                        new google.maps.Point(13, 42)
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

                    google.maps.event.addListener(marker, 'click', function () {
                        infowindow.open(self.map, self.markersArray[number]);
                    });

                });

                $('.' + settings.moveToLocationClass).click(function () {
                    number = $(this).attr('rel');
                    contentString = self.getHtmlPopap(
                        $(this).parent().find('.titleAddress').html(),
                        $(this).parent().find('.contentAddress').html()
                    );
                    var infowindow = new google.maps.InfoWindow({
                        content: contentString
                    });

                    infowindow.open(self.map, self.markersArray[number]);
                    return false;
                });
            }
            //end setup markers
            var marker = new google.maps.Marker({
                map: self.map
            });

            function validateAddress() {
                self.infowindow.close();
                marker.setVisible(false);
                input.className = '';
                self.place = self.autocomplete.getPlace();
                //Inform the user that the place was not found and return.
                if (!self.place.geometry) {
                    self.isValid = false;
                    return;
                } else {
                    self.isValid = true;
                }
                //If the place has a geometry, then present it on a map.
                if (self.place.geometry.viewport) {
                    self.map.fitBounds(self.place.geometry.viewport);
                } else {
                    self.map.setCenter(self.place.geometry.location);
                    self.map.setZoom(15);  // Why 17? Because it looks good.
                }
                marker.setIcon(/** @type {google.maps.Icon} */({
                    url: self.place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(35, 35)
                }));
                marker.setPosition(self.place.geometry.location);
                marker.setVisible(false);
                var address = '';
                if (self.place.address_components) {
                    address = [
                        (self.place.address_components[0] && self.place.address_components[0].short_name || ''),
                        (self.place.address_components[1] && self.place.address_components[1].short_name || '')
                    ].join(' ');
                }
                var htmlPopup = self.getHtmlPopap(self.place.name, address);
                self.infowindow.setContent(htmlPopup);
                self.infowindow.open(self.map, marker);
                initialCheck();
            }

            $('#' + settings.findInputId).change(function () {
                $(this).addClass('notfound');
                settings.changeSearch();
            });

            function afterAddProperty() {
                if (settings.autoHideLoadingSpinner === false) {
                    return;
                }
                $('#' + settings.findButtonId).removeClass('grey');
                $('#' + settings.findButtonId).removeClass('disabled');
                if (settings.loadingSpinner) {
                    $('#' + settings.findButtonId).parent().find('.' + settings.loadingSpinnerClass).hide();
                }

                if (!settings.divIdError && !settings.classError) {
                    return;
                }

                if (settings.divIdError) {
                    $('#' + settings.divIdError).hide();
                } else {
                    $('.' + settings.classError).hide();
                }
            }

            function executeSearch(data) {
                if (typeof data == 'undefined') {
                    self.showError(Translator.trans('select.from.drop.down.list'));
                    return false;
                }

                if (data.address.length < 5) {
                    self.showError(Translator.trans('fill.full.address'));
                    return false;
                }

                if (settings.loadingSpinner) {
                    $('#' + settings.findButtonId).parent().find('.' + settings.loadingSpinnerClass).show();
                }

                jQuery.ajax({
                    url: settings.linkAddProperty,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'stringAddress': data.stringAddress
                    },
                    error: function (jqXHR, errorThrown, textStatus) {
                        afterAddProperty();
                        self.showError(Translator.trans('fill.full.address'));
                        settings.addPropertyCallbackNotValid.call(self, jqXHR, errorThrown, textStatus);
                        return false;
                    },
                    success: function (data, textStatus, jqXHR) {
                        if (data.status == "OK") {
                            settings.addPropertyCallback.call(self, data, textStatus, jqXHR);
                            afterAddProperty();
                        } else {
                            self.showError(data.message);
                            settings.addPropertyCallbackNotValid.call(self, data, textStatus, jqXHR);
                        }
                    }
                });
            }

            function initialCheck() {
                if (settings.divIdError !== false) {
                    $('#' + settings.divIdError).hide();
                }

                if (settings.classError !== false) {
                    $('.' + settings.classError).hide();
                }

                if ($('#' + settings.findButtonId).hasClass('grey')) {
                    return false;
                }

                $('#' + settings.findButtonId).addClass('disabled grey');

                if ('' == $('#' + settings.findInputId).val()) {
                    self.showError(Translator.trans('error.property.empty'));
                    return false;
                }

                if (self.data.length > 0) {
                    self.data = [];
                }

                self.place = self.autocomplete.getPlace();
                var stringAddress = $('#' + settings.findInputId).val();
                if (typeof self.place != 'undefined' && typeof self.place.address_components != 'undefined') {
                    self.data = {
                        'stringAddress': stringAddress,
                        'address': self.place.address_components,
                        'geometry': self.place.geometry
                    };
                    executeSearch(self.data);
                } else {
                    self.infowindow.close();
                    var geocoder = new google.maps.Geocoder();
                    geocoder.geocode({"address": stringAddress}, function (results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            var lat = results[0].geometry.location.lat(),
                                lng = results[0].geometry.location.lng(),
                                placeName = results[0].address_components[0].long_name,
                                latlng = new google.maps.LatLng(lat, lng);

                            marker.setPosition(latlng);
                            var title = results[0].address_components[0].long_name + results[0].address_components[1].long_name;
                            var address = '';
                            var htmlPopup = self.getHtmlPopap(title, address);
                            self.infowindow.setContent(htmlPopup);
                            self.infowindow.open(self.map, marker);

                            $("#" + settings.findInputId).val(stringAddress);
                            self.data = {
                                'stringAddress': stringAddress,
                                'address': results[0].address_components,
                                'geometry': results[0].geometry
                            };
                            executeSearch(self.data);
                        } else {
                            self.showError(Translator.trans('select.from.drop.down.list'));
                        }
                    });
                }
            }

            $('#' + settings.findInputId).keypress(function (e) {
                if (e.which == 13) {
                    initialCheck();
                }
            });

            $('#' + settings.findButtonId).click(function () {
                initialCheck();
            });

            google.maps.event.addListener(self.autocomplete, 'place_changed', validateAddress);
        }


        google.maps.event.addDomListener(window, 'load', initialize);

        if (settings.formId) {
            $(document).delegate('#' + settings.formId, "submit", function () {
                return false;
            });
        }
        return self;
    };

}(jQuery, google));
