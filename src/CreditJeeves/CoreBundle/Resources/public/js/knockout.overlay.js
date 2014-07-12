// it does not work need to change jQuery plugin
ko.bindingHandlers.overlay = function() {
    this.init = function(element, valueAccessor, allBindingsAccessor) {
        var options = ko.utils.unwrapObservable(valueAccessor().overlayOptions) || {};
        var el = jQuery(element);
        options = jQuery.extend(
            {
                effect: 'fade',
                overlayClass: 'overlay',
                glossy: false,
                autoOpen: false,
                container: el,
                zIndex: 3000,
                onShow: function() {
                    jQuery(this).click(function(evt) {
                        evt.preventDefault();
                    }).bind('contextmenu', function(evt) {
                        evt.preventDefault();
                    });
                }
            },
            options
        );
        el.overlay(options);

        //handle disposal (not strictly necessary in this scenario)
        ko.utils.domNodeDisposal.addDisposeCallback(element, function() {
            el.overlay('destroy');
        });
    };
    this.update = function(element, valueAccessor, allBindingsAccessor) {
        var shouldBeOpen = ko.utils.unwrapObservable(allBindingsAccessor()),
            el = jQuery(element);
        if (shouldBeOpen) {
            el.overlay('open');
        } else {
            el.overlay('close');
        }
    };
}();
