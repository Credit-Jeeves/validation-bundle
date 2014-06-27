ko.bindingHandlers.datepickerOld = {
    init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
        //initialize datepicker with some optional options
        var options = allBindingsAccessor().datepickerOptions || {};
        if (typeof(bindingContext.$parent.uniqueId)  == "function") {
            $(element).attr('id', bindingContext.$parent.uniqueId);
        }

        $(element).datepicker(options);

        //handle the field changing
        ko.utils.registerEventHandler(element, "change", function () {
            var observable = valueAccessor();
            var datepickerFieldName = allBindingsAccessor().datepickerFieldName || '';

            if (typeof(bindingContext.$parent.setDateDatepickerIntoRow)  == "function") {
                bindingContext.$parent.setDateDatepickerIntoRow(viewModel, element, datepickerFieldName);
                return;
            }


            if (typeof(observable) == "function") {
                observable($(element).datepicker("getDate"));
                return;
            }

            console.info("Define setDateDatepickerIntoRow function in your model");
        });

        //handle disposal (if KO removes by the template binding)
        ko.utils.domNodeDisposal.addDisposeCallback(element, function() {
            $(element).datepicker("destroy");
        });

    },
    //update the control when the view model changes
    update: function(element, valueAccessor) {
        var value = ko.utils.unwrapObservable(valueAccessor()),
            current = $(element).datepicker("getDate");

        if (value - current !== 0) {
            $(element).datepicker("setDate", value);
        }
    }
};

// <input data-bind="datepicker: myDate, datepickerOptions: { minDate: new Date() }" />
// @url http://jsfiddle.net/rniemeyer/NAgNV/
ko.bindingHandlers.datepicker = {
    init: function(element, valueAccessor, allBindingsAccessor) {
        //initialize datepicker with some optional options
        var options = allBindingsAccessor().datepickerOptions || {};
        var el = jQuery(element);
        var defaultOptions = {
            showOn: "both",
            buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
            buttonImageOnly: true,
            showOtherMonths: true,
            selectOtherMonths: true
        };
        options = jQuery.extend(defaultOptions, options);
        el.datepicker(options);

        //handle the field changing
        ko.utils.registerEventHandler(element, "change", function () {
            var observable = valueAccessor();
            observable(el.datepicker("getDate"));
        });

        //handle disposal (if KO removes by the template binding)
        ko.utils.domNodeDisposal.addDisposeCallback(element, function() {
            el.datepicker("destroy");
        });

    },
    update: function(element, valueAccessor) {
        var value = ko.utils.unwrapObservable(valueAccessor());
        var el = jQuery(element);

        //handle date data coming via json from Microsoft
        if (String(value).indexOf('/Date(') == 0) {
            value = new Date(parseInt(value.replace(/\/Date\((.*?)\)\//gi, "$1")));
        }

        var current = el.datepicker("getDate");

        if (value - current !== 0) {
            el.datepicker("setDate", value);
        }
    }
};
