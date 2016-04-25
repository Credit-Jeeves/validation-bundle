/**
 * Component infoMessage-widget, can be used like container for show information (yellow) message
 *  - Can observe internal external parameter and change message text
 *  - Can have initial message text
 *  - Can use external (calculable) condition for visibility
 * name : 'infoMessage-widget'
 * params
 *  - message - external observable parameter
 *  - initialMessage
 *  - visibleConditions
 */
ko.components.register('infoMessage-widget', {
    viewModel: function(params) {
        this.message = (params && params.message !== undefined) ?
            ko.computed(function () {
                if (params.message() === null) {
                    params.message(params.initialMessage || '');
                }
                return params.message();
            }, this) :
            ko.observable(
                (params && params.initialMessage) || ''
            );
        this.isVisible = ko.computed(function () {
            if (params && params.visibleConditions !== undefined) {
                return params.visibleConditions && this.message();
            }
            return this.message();
        }, this);
    },
    template:
        '<div class="information-box pie-el"' +
        ' data-bind="visible: isVisible(), text: message">' +
        '</div>',
    synchronous: true
});

/**
 * Component virtualForm-widget
 * name : 'virtualForm-widget'
 * params
 *  - elements
 *  - method
 *  - url
 */
function VirtualFormViewModel(params) {
    this.elements = (params && params.elements !== undefined) ?
        ko.computed(function () {
            return typeof(params.elements) == 'function' ? params.elements() : params.elements;
        }, this) :
        ko.observableArray([]);
    this.method = (params && params.method !== undefined) ?
        ko.computed(function () {
            return typeof(params.method) == 'function' ? params.method() : params.method;
        }, this) :
        ko.observable('get');
    this.url = (params && params.url !== undefined) ?
        ko.computed(function () {
            return typeof(params.url) == 'function' ? params.url() : params.url;
        }, this) :
        ko.observable('');
    this.submitHandler = function() {
        var form = document.getElementById("virtualForm-widget");
        if (form) {
            form.submit();
        }
    };
}
ko.components.register('virtualForm-widget', {
    viewModel: VirtualFormViewModel,
    template:
        '<form id="virtualForm-widget"' +
            ' data-bind="attr: {method: method, action : url}, foreach: elements"' +
            ' style="display:none;">' +
            '<input type="hidden" data-bind="attr: {name: name}, value: value">' +
        '</form>'
});
