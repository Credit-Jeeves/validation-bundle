// <input data-bind="datepicker: myDate, datepickerOptions: { minDate: new Date() }" />
// @url http://jsfiddle.net/rniemeyer/NAgNV/
ko.bindingHandlers.datepicker = {
    init: function(element, valueAccessor, allBindingsAccessor) {
        //initialize datepicker with some optional options
        var options = allBindingsAccessor().datepickerOptions || {};
        var el = jQuery(element);
        options = jQuery.extend(
            {
                buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
                buttonImageOnly: true,
                showOtherMonths: true,
                selectOtherMonths: true
            },
            options
        );

        //handle the field changing
        ko.utils.registerEventHandler(element, "change", function () {
            var observable = valueAccessor();
            observable(el.datepicker().val());
        });

        //handle disposal (if KO removes by the template binding)
        ko.utils.domNodeDisposal.addDisposeCallback(element, function() {
            el.datepicker("destroy");
        });

        //handle the options changing
        ko.utils.registerEventHandler(element, "updateOptions", function (event, newOptions) {
            for (var option in newOptions) {
                if (newOptions.hasOwnProperty(option)) {
                    el.datepicker("option", option, newOptions[option]);
                }
            }
        });

        el.datepicker(options);
    },
    update: function(element, valueAccessor) {
        var observable = valueAccessor();
        var value = ko.utils.unwrapObservable(observable);
        var el = jQuery(element);

        if (value != el.datepicker().val()) {
            el.datepicker().val(value);
            if (value != el.datepicker().val()) {
                observable(el.datepicker().val());
            }
        }
    }
};

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
