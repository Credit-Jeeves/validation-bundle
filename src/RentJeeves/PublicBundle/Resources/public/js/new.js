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
    var markersArray = [];
    var rentaPiontShadow = new google.maps.MarkerImage('/bundles/rjpublic/images/ill-renta-point_shadow.png',
              new google.maps.Size(38,54),
              new google.maps.Point(0,0),
              new google.maps.Point(19, 41)
            );

    function deleteOverlays() 
    {
      if (markersArray) {
        for (i in markersArray) {
          markersArray[i].setMap(null);
        }
      }
    }

    function getHtmlPopap(title, content)
    {
      return  '<div id="content">'+
              '<div id="siteNotice">'+
              '</div>'+
              '<h1 id="firstHeading" class="firstHeading">'+title+'</h1>'+
              '<div id="bodyContent" style="width:150px;">'+content 
              '<p></div>'+
              '</div>';
    }

    function showError(message)
    {
        return alert(message);
    }


    function search(place, map) 
    {
        if($('#search-submit').hasClass('grey')) {
          return false;
        }
        var data = {'address': place.address_components, 'geometry':place.geometry, 'addGroup': 0};
        $('#search-submit').addClass('disabled grey');
        $('#search-submit').find('.loadingSpinner').show();

        jQuery.ajax({
          url: Routing.generate('landlord_property_add'),
          type: 'POST',
          dataType: 'json',
          data: {'data': JSON.stringify(data, null)},
          error: function(jqXHR, errorThrown, textStatus) {;
          },
          success: function(data, textStatus, jqXHR) {
            if(data.hasLandlord) {
              return location.href = Routing.generate('iframe_search_check', {'propertyId':data.property.id });
            } 
            
            $('#search-submit').removeClass('disabled grey');
            $('#search-submit').find('.loadingSpinner').hide();

            $('.search-result-text').find('h4').hide();
            $.each($('.addressText'), function(index, value) {
              $(this).hide();
            });

            deleteOverlays();
            var link = Routing.generate('iframe_search_check', {'propertyId': data.property.id });
            $('.notFound').show();
            $('.notFound').find('.titleAddress').html(data.property.number+' '+data.property.street);
            $('.notFound').find('.contentAddress').html(data.property.city+', '+data.property.area+' '+data.property.zip);
            $('.notFound').find('.inviteLandlord').attr('href', link);
            $('.notFound').parent().parent().find('.titleNotFound').show();
            $('.notFound').parent().parent().find('.titleSearch').hide();
            var contentString = getHtmlPopap(
              $('.notFound').find('.titleAddress').html(),
              $('.notFound').find('.contentAddress').html()
            );
            
            contentString += '<hr /><a href="'+link+'" class="button small inviteLandlord" >';
            contentString += '<span>Invite Your Landlord</span></a>';

            var infowindow = new google.maps.InfoWindow({
              content: contentString
            });

            var rentaPoint = new google.maps.MarkerImage('/bundles/rjpublic/images/ill-renta-point_1.png',
              new google.maps.Size(26,42),
              new google.maps.Point(0,0),
              new google.maps.Point(13,42)
            );

            var marker = new google.maps.Marker({
                position: new google.maps.LatLng(data.property.jb,data.property.kb),
                map: map,
                title: data.property.address,
                icon: rentaPoint,
                shadow: rentaPiontShadow
            });

            markersArray['notfound'] = marker;

            google.maps.event.addListener(marker, 'click', function() {
              infowindow.open(map,marker);
            });

            //If the place has a geometry, then present it on a map.
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(15);  // Why 17? Because it looks good.
            }
          }
        });
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

            var contentString = getHtmlPopap(
              $(this).find('.titleAddress').html(),
              $(this).find('.contentAddress').html()
            );

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

            markersArray[number] = marker;

            google.maps.event.addListener(marker, 'click', function() {
              infowindow.open(map,markersArray[number]);
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
              return false;
          } 
        }
        
        $('#property-search').change(function(){
          $(this).addClass('notfound');
          if($(this).val() != '') {
            $('#delete').show();
          } else {
            $('#delete').hide();
          }
        });

        $('#search-submit>span').click(function(){
            var place = autocomplete.getPlace();
            $('#propertyId').val('');
            $('#register').addClass('greyButton');

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

        $('.moveToLocation').click(function(){
            number =  $(this).attr('rel');
            var contentString = getHtmlPopap(
              $(this).parent().find('.titleAddress').html(),
              $(this).parent().find('.contentAddress').html()
            );

            var infowindow = new google.maps.InfoWindow({
              content: contentString
            });
   
            infowindow.open(map, markersArray[number]);
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
      $('#property-search').val(' ');
      return false;
    });

    $('#register').click(function(){
      var propertyId = $('#propertyId').val();
      if(propertyId == '') {
        showError('Please select your rental');
        return false;
      }
    });

    $('.thisIsMyRental').click(function(){
        if($(this).hasClass('match')) {
          propertyId = $(this).attr('data');
          $.each($('.addressText'), function(index, value) {
              var id = $(this).attr('data');
              if(id != propertyId) {
                $(this).show();
              } else {
                $(this).css({backgroundColor:'#FFFFFF'});
              }
          });
          
          $('#propertyId').val('');
          $('#register').addClass('greyButton');
          initScroll();
          $(this).removeClass('match');
        } else {
          propertyId = $(this).attr('data');
          $.each($('.addressText'), function(index, value) {
              var id = $(this).attr('data');
              if(id != propertyId) {
                $(this).hide();
              } else {
                $(this).css({backgroundColor:'#EEEEEE'});
              }
          });
          
          $('#propertyId').val(propertyId);
          $('#register').removeClass('greyButton');
          initScroll();
          $(this).addClass('match');
          //$(this).hide();
        }
        return false;
    });
});