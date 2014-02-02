ko.bindingHandlers.i18n = function () {
    var translate = function(args, i18nKey) {
        var str = Translator.transChoice(i18nKey.replace(/^\s+|\s+$/g, ''));
        if (!str) {
            str = i18nKey;
        }
        for (var val in args) {
            if (!args.hasOwnProperty(val)) continue;
            str = str.replace(new RegExp('%' + val + '%', "g"), ko.unwrap(args[val]));
        }

        return str;
    };
    return {
        init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
            // This will be called when the binding is first applied to an element
            // Set up any initial state, event handlers, etc. here
            var underlyingObservable = valueAccessor();

            var i18nKey = $(element).attr('i18n');
            if (!i18nKey) {
                $(element).attr('i18n', i18nKey = $(element).html());
            }

            var interceptor = ko.computed({
                read: function() {
                    // this function does get called, but it's return value is not used as the value of the textbox.
                    // the raw value from the underlyingObservable, or the actual value the user entered is used instead, no
                    // dollar sign added. It seems like this read function is completely useless, and isn't used at all
                    var returnString = translate(underlyingObservable, i18nKey);
                    return returnString;
                },
                write: function(newValue) {
                    var current = underlyingObservable();
                    var valueToWrite = translate(current, i18nKey);

                    if (valueToWrite !== current) {
                        // for some reason, if a user enters 20.00000 for example, the value written to the observable
                        // is 20, but the original value they entered (20.00000) is still shown in the text box.
                        underlyingObservable(valueToWrite);
                    } else {
                        if (newValue !== current.toString())
                            underlyingObservable.valueHasMutated();
                    }
                }
            });

            ko.applyBindingsToNode(element, { html: interceptor });
        },
        update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
            // This will be called once when the binding is first applied to an element,
            // and again whenever the associated observable changes value.
            // Update the DOM element based on the supplied values here.
            ko.applyBindingsToNode(element, { html: translate(valueAccessor(), $(element).html())});
        }
    };
}();
