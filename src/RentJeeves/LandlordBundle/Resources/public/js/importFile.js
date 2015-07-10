function importFile() {
    this.source = ko.observable();
    this.importType = ko.observable();
    this.integrationType = ko.observable();

    this.source.subscribe( function (newValue) {
        var el = $('#import_file_type_importType > option[value="multi_groups"]');

        if ('integrated_api' === newValue) {
            el.attr('disabled', true);
        } else {
            el.attr('disabled', false);
        }
    });
}
