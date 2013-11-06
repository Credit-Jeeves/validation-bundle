$(document).ready(function(){
    $('#property-search').google({
        autoHideLoadingSpinner: true,
        divIdError: "errorSearchIframe",
        addPropertyCallback: function(data, textStatus, jqXHR){
            var isInIFrame = (window.location != window.parent.location);
            var location = Routing.generate('iframe_search_check', {'propertyId':data.property.id});

            if (data.isLogin && data.isLandlord) {
                var location = Routing.generate('landlord_properties');
            }

            if (isInIFrame == true) {
                // iframe
                window.parent.location.href = location;
            } else {
                // no iframe
                window.location.href = location;
            }
        }
    });
});