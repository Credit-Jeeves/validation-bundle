function Simulation() {

    var self = this;
    var _formId = 'simulator_form';

    this.is_dealer_side = ko.observable(false);
    this.type = ko.observable('');
    this.input = ko.observable(0);
    this.score_init = ko.observable(0);
    this.score_best = ko.observable(0);
    this.score_current = ko.observable(0);
    this.score_target = ko.observable(0);
    this.cash_used = ko.observable(0);
    this.sim_type = ko.observable(0);
    this.sim_type_group = ko.observable('');
    this.message = ko.observable('');
    this.blocks = ko.observableArray([]);
    this.title = ko.observable('');
    this.title_message = ko.observable('');

    this.getFico = ko.computed(function() {
        if (isNaN(self.score_best())) {
            return '';
        }
        var nFicoScore = Math.round(10 * ((self.score_best() - 483.06) / 11.079) + 490);
        return nFicoScore > 850 ? 850 : nFicoScore;
    });

    this.init = function() {
        sendData({});
    };


    var sendData = function(data) {
        jQuery('#simulation-container').showOverlay();
        var form = jQuery('#' + _formId);

        jQuery.ajax({
            url: '/_dev.php/atb/simulate', // TODO Routing.generate('experian_atb_simulate'),
            type: 'POST',
            timeout: 85000, // 85 secs
            dataType: 'json',
            data: data,
            error: function(jqXHR, errorThrown, textStatus) {
                jQuery('#simulation-container').hideOverlay();
            },
            success: function(data, textStatus, jqXHR) {

                ko.mapping.fromJS(data, {}, self);

                jQuery('#simulation-container').hideOverlay();
            }
        });
    };

    jQuery('#' + _formId).submit(function() {
        var formData = jQuery('#' + _formId).serializeArray();

        var intRegex = /^\d+$/;

        if (!intRegex.test(formData[0].value) || 0 >= formData[0].value) {
            jQuery('#simulation_errors').show();
            return false;
        }

        var data = jQuery.param(formData, false);
        sendData(data);

        jQuery('#simulation_errors').hide();

        return false;
    });
}
