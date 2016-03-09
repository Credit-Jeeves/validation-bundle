function LeaseListViewModel() {
    var self = this;

    self.nameFilter = ko.observable('');
    self.emailFilter = ko.observable('');
    self.addressFilter = ko.observable('');
    self.unitFilter = ko.observable('');

    self.errorMessage = ko.observable('');
    self.isUpdatingData = ko.observable(false);

    self.contracts = ko.observableArray([]);

    /**
     * Get data from db
     */
    self.loadData = function () {
        if (!self.nameFilter() && !self.emailFilter() && !self.addressFilter() && !self.unitFilter()) {
            return; // all filters are empty
        }
        if ((self.emailFilter() && self.emailFilter().length < 3) || (self.addressFilter() && self.addressFilter().length < 3)) {
            self.errorMessage(Translator.trans('landlord.scanning.lease_list.fields_error'));
            return; // all filters are empty
        }
        self.errorMessage(null);
        self.isUpdatingData(true);

        $.ajax({
            url: Routing.generate('landlord_scanning_check_filter_leases'),
            type: 'POST',
            dataType: 'json',
            data: {
                'name': self.nameFilter(),
                'email': self.emailFilter(),
                'address': self.addressFilter(),
                'unit': self.unitFilter()
            },
            success: function (data) {
                if (data.errorMessage != null) {
                    self.errorMessage(data.errorMessage);
                } else {
                    self.contracts(data.contracts);
                }
            },
            complete: function (data) {
                self.isUpdatingData(false);
            }
        });
    }
}
