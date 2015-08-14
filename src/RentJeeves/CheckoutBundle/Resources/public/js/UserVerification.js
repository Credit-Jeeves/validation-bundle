function UserVerification(parent, isPidVerificationSkipped) {
    var self = this;

    self.questions = ko.observable(null);

    self.isValidUser = ko.observable(true);

    self.isPidVerificationSkipped = isPidVerificationSkipped;

    self.isProcessQuestion = false;

    self.loadQuestions = function() {
        jQuery('#pay-popup').showOverlay();
        jQuery.ajax({
            url: Routing.generate('experian_pidkiq_get'),
            type: 'POST',
            timeout: 30000, // 30 secs
            error: function(jqXHR, textStatus, errorThrown) {
                jQuery('#pay-popup').hideOverlay();
                window.formProcess.reLogin(jqXHR, errorThrown);
                window.formProcess.addFormError('#vi-questions', errorThrown);
            },
            success: function(data) {
                jQuery('#pay-popup').hideOverlay();
                if (data['isValidUser'] !== undefined && data['isValidUser'] === false) {
                    self.isValidUser(false);
                } else {
                    self.isValidUser(true);
                }
                if (data['status'] && 'error' == data['status']) {
                    window.formProcess.addFormError('#vi-questions', data['error']);
                    self.isProcessQuestion = true;
                    return;
                }
                self.questions(data);
            }
        });
    };
}
