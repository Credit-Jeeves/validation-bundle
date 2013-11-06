$(document).ready(function(){
    var google = $('#property-search').google({
        formId: "formSearch",
        findButtonId: "search-submit",
        mapCanvasId: 'map-canvas',
        loadingSpinner: true,
        loadingSpinnerClass: 'loadingSpinner',
        autoHideLoadingSpinner: true,
        addPropertyCallback: function(data, textStatus, jqXHR){
            if(data.hasLandlord) {
                return location.href = Routing.generate('property_add_id', {'propertyId':data.property.id });
            } else {
                return location.href = Routing.generate('tenant_invite_landlord', {'propertyId':data.property.id });
            }
        },
        divIdError: 'errorSearch',
        clearSearchId: 'delete'
    });
});