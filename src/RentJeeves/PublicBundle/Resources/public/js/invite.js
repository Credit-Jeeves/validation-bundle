$(document).ready(function(){
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


    $('#property-search').google({
        autoHideLoadingSpinner: true,
        mapCanvasId: "map-canvas",
        formId: "formSearch",
        markers: false,
        clearSearchId: 'delete',
        divIdError: "errors",
        findButtonId: 'search-submit',
        addPropertyCallback: function(data, textStatus, jqXHR){
            if (data.isLogin && data.isLandlord) {
                location.href = Routing.generate('landlord_properties');
                return;
            }
            location.href = Routing.generate('iframe_search_check', {'propertyId':data.property.id});
        }
    });
});
