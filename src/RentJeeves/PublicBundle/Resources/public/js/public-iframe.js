var propertyId = '';
$(document).ready(function(){
    $('#iframe-tenant-button').click(function(){
        window.open(Routing.generate('iframe_new', {'propertyId': propertyId}), '_blank');
    });
    $('#iframe-landlord-button').click(function(){
        window.open(Routing.generate('landlord_register'), '_blank');
    });
});
