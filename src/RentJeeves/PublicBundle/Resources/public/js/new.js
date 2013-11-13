$(document).ready(function(){

    function initScroll() {
      $('#search-result-text').slimScroll({
        alwaysVisible:true,
        width:307,
        height:295
      });
    }

    initScroll();
    
    var google = $('#property-search').google({
        mapCanvasId: "search-result-map",
        markers: true,
        clearSearchId: 'delete',
        findButtonId: 'search-submit',
        defaultLat: $('#lat').val(),
        defaultLong: $('#lng').val(),
        classError :'errorsGoogleSearch',
        addPropertyCallback: function(data, textStatus, jqXHR) {
            if(data.hasLandlord) {
                return location.href = Routing.generate('iframe_search_check', {'propertyId':data.property.id });
            }

            $('#search-submit').removeClass('disabled grey');
            $('#search-submit').find('.loadingSpinner').hide();

            $('.search-result-text').find('h4').hide();
            $.each($('.addressText'), function(index, value) {
                $(this).hide();
            });

            var link = Routing.generate('iframe_search_check', {'propertyId': data.property.id });
            $('.notFound').show();
            $('.notFound').find('.titleAddress').html(data.property.number+' '+data.property.street);
            $('.notFound').find('.contentAddress').html(data.property.city+', '+data.property.area+' '+data.property.zip);
            $('.notFound').find('.inviteLandlord').attr('href', link);
            $('.notFound').parent().parent().find('.titleNotFound').show();
            $('.notFound').parent().parent().find('.titleSearch').hide();

            var contentString = this.getHtmlPopap(
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
                map: this.map,
                title: data.property.address,
                icon: rentaPoint,
                shadow: this.rentaPiontShadow
            });

            this.markersArray['notfound'] = marker;

            google.maps.event.addListener(marker, 'click', function() {
                infowindow.open(this.map, marker);
            });

            //If the place has a geometry, then present it on a map.
            if (typeof this.place.geometry.viewport != 'undefined') {
                this.map.fitBounds(this.place.geometry.viewport);
            } else if (typeof this.place.geometry.location != 'undefined') {
                this.map.setCenter(this.place.geometry.location);
                this.map.setZoom(15);  // Why 15? Because it looks good.
            }
        }
    });

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

    $('#register').click(function(){
      var propertyId = $('#propertyId').val();
      if(propertyId == '') {
        $('html, body').animate({
              scrollTop: $(".search-box").offset().top
        }, 800);
        google.showError(Translator.get('select.rental'));
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
    
    $(function() {
      $("#pricing-popup").dialog({
        width:660,
        autoOpen: false,
        modal:true
      });
    });

    $('#popup-pricing').click(function(){
      $("#pricing-popup").dialog('open');
    });

    $('#pricing-popup button.button-close').click(function(){
      $("#pricing-popup").dialog('close');
    });
});