$(document).ready(function(){

    function initScroll() {
      $('#search-result-text').slimScroll({
        alwaysVisible:true,
        width:307,
        height:295
      });
    }

    initScroll();
    
    var ERROR = 'notfound';

    function showError(message)
    {
        alert(message);
    }

    function initialize() {
        var lat = $('#lat').val();
        var lng = $('#lng').val();


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
        var rentaPiontShadow = new google.maps.MarkerImage('/bundles/rjpublic/images/ill-renta-point_shadow.png',
              new google.maps.Size(38,54),
              new google.maps.Point(0,0),
              new google.maps.Point(19, 41)
            );
        
        var arrBubble = [];
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

            var contentString = '<div id="content">'+
              '<div id="siteNotice">'+
              '</div>'+
              '<h1 id="firstHeading" class="firstHeading">'+$(this).find('.titleAddress').html()+'</h1>'+
              '<div id="bodyContent" style="width:150px;">'+$(this).find('.contentAddress').html() 
              '<p></div>'+
              '</div>';
          
            arrBubble[number] = new InfoBubble({
              map: map,
              content: contentString,
              position: myLatlng,
              shadowStyle: 1,
              padding: 10,
              backgroundColor: '#FFFFFF',
              borderRadius: 4,
              arrowSize: 20,
              borderWidth: 3,
              borderColor: '#A9A9A9',
              disableAutoPan: true,
              hideCloseButton: false,
              arrowPosition: 40,
              backgroundClassName: 'phoney',
              arrowStyle: 1,
              infoBoxClearance: new google.maps.Size(1, 1)
            });

            var infowindow = new google.maps.InfoWindow({
              content: contentString
            });

            var marker = new google.maps.Marker({
                position: myLatlng,
                map: map,
                title: addressSelect,
                icon: rentaPoint,
                shadow: rentaPiontShadow
            });

            google.maps.event.addListener(marker, 'click', function() {
              infowindow.open(map,marker);
              //newlat = marker.getPosition().lat() + (0.00002 * Math.pow(2, (21 - map.getZoom())));
              //arrBubble[number].setPosition(new google.maps.LatLng(newlat, marker.getPosition().lng()));
              //arrBubble[number].open();
            });

        });
        
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
          if($(this).val() != '') {
            $('#delete').show();
          } else {
            $('#delete').hide();
          }
        });

        $('#search-submit').click(function(){
            if (ERROR == $('#property-search').attr('class')) {
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
              error: function(jqXHR, errorThrown, textStatus) {;
              },
              success: function(propertyId, textStatus, jqXHR) {
                location.href = Routing.generate('iframe_search_check', {'propertyId':propertyId});
              }
            });
            
            return false;
        });

        google.maps.event.addListener(autocomplete, 'place_changed', validateAddress);
    }

    $('#formSearch').submit(function() {
      return false;
    });

    google.maps.event.addDomListener(window, 'load', initialize);

    $('.select-unit').change(function(){
      val = $(this).val();
      if(val == 'new') {
        $(this).parent().hide();
        $(this).parent().parent().find('.createNewUnit').show();
        $(this).parent().parent().find('.lab1').show();
        $(this).parent().parent().find('.lab2').hide();
      }
    });

    $('.see-all').click(function() {
      $(this).parent().parent().find('.selectUnit').show();
      $(this).parent().hide();
      $(this).parent().parent().find('.select-unit:selected').prop("selected", false);
      $(this).parent().parent().find('.noneField').attr('selected', true);
      $(this).parent().parent().find('.lab2').show();
      $(this).parent().parent().find('.lab1').hide();
      return false;
    });

    $('#delete').click(function() {
      $('#property-search').val(' ')
    });

    $('#register').click(function(){
      var propertyId = $('#propertyId').val();
      if(propertyId == '') {
        showError('Please select your rental');
        return false;
      }
    });

    $('.thisIsMyRental').click(function(){
        propertyId = $(this).attr('data');
        $.each($('.addressText'), function(index, value) {
            var id = $(this).attr('data');
            if(id != propertyId) {
              $(this).hide();
            }
        });
        $('#propertyId').val(propertyId);
        $('#register').removeClass('greyButton');
        initScroll();
        $(this).hide();
        return false;
    });
});