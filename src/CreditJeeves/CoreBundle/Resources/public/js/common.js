$(document).ready(function(){
  // $.browser is deprecated from version 1.3 and absent in version 1.9
  $.uaMatch = function( ua ) {
    ua = ua.toLowerCase();

    var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
      /(webkit)[ \/]([\w.]+)/.exec( ua ) ||
      /(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
      /(msie) ([\w.]+)/.exec( ua ) ||
      ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
      [];

    return {
      browser: match[ 1 ] || "",
      version: match[ 2 ] || "0"
    };
  };

  // Don't clobber any existing jQuery.browser in case it's different
  if ( !$.browser ) {
    matched = $.uaMatch( navigator.userAgent );
    browser = {};

    if ( matched.browser ) {
      browser[ matched.browser ] = true;
      browser.version = matched.version;
    }

    // Chrome is Webkit, but Webkit is also Safari.
    if ( browser.chrome ) {
      browser.webkit = true;
    } else if ( browser.webkit ) {
      browser.safari = true;
    }

    $.browser = browser;
  }
  // Now we could use linkselect plugin
  $('select:not(.original)').linkselect();
  
  jQuery('input[placeholder], textarea[placeholder]').placeholder();
  
  jQuery('.user-ssn').ssn();

  
  
});

ko.bindingHandlers.i18n = {
    init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
      // This will be called when the binding is first applied to an element
      // Set up any initial state, event handlers, etc. here
      var str = $(element).html();
        console.log(str);
      var args = valueAccessor();
      for (var val in args) {
        if (!args.hasOwnProperty(val)) continue;
        str = str.replace(new RegExp('%' + val + '%', "g"), args[val]);
      }
      $(element).html(str);
    },
    update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
        // This will be called once when the binding is first applied to an element,
        // and again whenever the associated observable changes value.
        // Update the DOM element based on the supplied values here.
    }
};    
