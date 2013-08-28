function Info(ReportingViewModel) {
    this.popup = ko.observable(false);
    this.openPopup = function() {
        ReportingViewModel.process(true);
    };
}
