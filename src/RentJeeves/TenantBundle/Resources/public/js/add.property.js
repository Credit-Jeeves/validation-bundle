$(document).ready(function(){

    function initScroll() {
      $('.slimScrollDiv').css('height','310px');
      $('#search-result-text').css('height','310px');
      $('#search-result-text').slimScroll({
        alwaysVisible:true,
        width:307,
        height:310
      });
    }
    function destroySlimscroll(objectId) {
        $("#"+objectId).parent().replaceWith($("#"+objectId));
    }

    function hideError()
    {
        $('#errorSearch').hide();
        destroySlimscroll('search-result-text');
        $('#search-result-text').slimscroll("destroy");
        $('#search-result-text').slimScroll({
            alwaysVisible:true,
            width:307,
            height:295
        });
        return;
    }

    function showError(message)
    {
        $('#errorSearch').show();
        $('#errorSearch').html('<h3 id="errorMessage">'+message+'</h3>');
        destroySlimscroll('search-result-text');
        $('#search-result-text').slimScroll({
            alwaysVisible:true,
            width:307,
            height:255
        });
        return;
    }

    initScroll();

    $('#formSearch').submit(function() {
      return false;
    });

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

    var lat = $('#lat').val();
    var lng = $('#lng').val();

    var google = $('#property-search').google({
        formId: "formSearch",
        findButtonId: "search-submit",
        mapCanvasId: 'search-result-map',
        loadingSpinner: true,
        loadingSpinnerClass: 'loadingSpinner',
        autoHideLoadingSpinner: true,
        addPropertyCallback: function(data, textStatus, jqXHR){
            return location.href = Routing.generate('property_add_id', {'propertyId':data.property.id });
        },
        addPropertyCallbackNotValid: function(jqXHR, errorThrown, textStatus){

        },
        clearSearchCallback: function(isEmpty){},
        changeSearch: function(){

        },
        markers: true,
        divIdError: 'searchError',
        defaultLat: lat,
        defaultLong: lng,
        clearSearchId: 'delete',
        clearSearchClass: null
    });

    $('#register').click(function(){
      var propertyId = $('#propertyId').val();
      if(propertyId == '') {
        showError(Translator.get('select.rental'));
        return false;
      }
    });

    function notFountTitle(isHide)
    {
        if ($('.notFoundTitle').length <= 0) {
            return;
        }

        if (isHide) {
            $('.notFoundTitle').hide();
            return;
        }

        $('.notFoundTitle').show();
    }



    $('.thisIsMyRental').click(function(){
        hideError();
        var titleNew = $(this).parent().parent().parent().parent().find('.titleMach').val();
        if($(this).hasClass('match')) {
          notFountTitle(false);
          $('.titleExistRental').html($('.titlebefore').val());
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
          notFountTitle(true);
          $(this).removeClass('greyTenant');
          $('.titlebefore').val($('.titleExistRental').html());
          $('.titleExistRental').html(titleNew);
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



});