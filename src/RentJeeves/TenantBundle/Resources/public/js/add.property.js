$(document).ready(function(){

    function initScroll() {
      $('#search-result-text').slimScroll({
        alwaysVisible:true,
        width:307,
        height:295
      });
    }

    function checkDeleteButton()
    {
      if($('#property-search').val() != '') {
        $('#delete').show();
      } else {
        $('#delete').hide();
      }
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
        var data = {'address': place.address_components, 'geometry':place.geometry, 'addGroup': 0};

        jQuery.ajax({
          url: Routing.generate('landlord_property_add'),
          type: 'POST',
          dataType: 'json',
          data: {'data': JSON.stringify(data, null)},
          error: function(jqXHR, errorThrown, textStatus) {;
          },
          success: function(data, textStatus, jqXHR) {
              return location.href = Routing.generate('property_add_id', {'propertyId':data.property.id });
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
          checkDeleteButton();
        });

        $('#search-submit>span').click(function(){
            var place = autocomplete.getPlace();
            $('#propertyId').val('');
            $('#register').addClass('greyButton');
            $('#register').addClass('disabled');
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
    $('.select-unit').linkselect('destroy');
    $('.select-unit').linkselect({
      change: function(li, val, text){
        var id = $(li).attr('id');
        var ids = id.split('_');
        if(val == 'new') {
          $('#'+ids[0]).parent().hide();
          $('#'+ids[0]).parent().parent().find('.createNewUnit').show();
          $('#'+ids[0]).parent().parent().find('.lab1').show();
          $('#'+ids[0]).parent().parent().find('.lab2').hide();
        }
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
          $(this).addClass('greyTenant');
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
          $('#register').addClass('disabled');
          initScroll();
          $(this).removeClass('match');
        } else {
          $(this).removeClass('greyTenant');
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
          $('#register').removeClass('disabled');
          initScroll();
          $(this).addClass('match');
          //$(this).hide();
        }
        return false;
    });

    checkDeleteButton();
});