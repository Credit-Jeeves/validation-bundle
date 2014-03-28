ko.bindingHandlers.datepicker = {
    init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
        //initialize datepicker with some optional options
        var options = allBindingsAccessor().datepickerOptions || {};

        $(element).datepicker(options);

        //handle the field changing
        ko.utils.registerEventHandler(element, "change", function () {
            var observable = valueAccessor();
            var datepickerFieldName = allBindingsAccessor().datepickerFieldName || '';

            if (typeof(bindingContext.$parent.setDateDatepickerIntoRow)) {
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
