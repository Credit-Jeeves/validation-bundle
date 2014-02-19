$(document).ready(function() {
    $('.next').click(function(){
        $('form').parent().showOverlay();
        return true;
    });
});
