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
