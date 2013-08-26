function Reporting() {
    var self = this;
    this.process = ko.observable(false);
    this.startReporting = function () {
        $.ajax({
            url: Routing.generate('tenant_reporting_start'),
            type: 'POST',
            dataType: 'json',
            data: {
                'action': 'start'
            },
            success: function (response) {
                location.reload();
            }
        });
    };
    this.closePopup = function() {
        self.process(false);
    };
}
