/**
 * $ Simple Overlay extension
 *
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
(function($) {

  $.fn.hideOverlay = function() {
    $(this).removeClass('overlay-trigger');
    this.find('.overlay').remove();
  };

  $.fn.showOverlay = function() {
    var self = this;

    this.hideOverlay();
    $(self).overlay({
      effect: 'fade',
      overlayClass: 'overlay',
      glossy: false,
      container: self,
      zIndex: 3000,
      onShow: function() {
        $(this).click(function(evt) {
          evt.preventDefault();
        }).bind('contextmenu', function(evt) {
          evt.preventDefault();
        });
      }
    });
  };

})(jQuery);
