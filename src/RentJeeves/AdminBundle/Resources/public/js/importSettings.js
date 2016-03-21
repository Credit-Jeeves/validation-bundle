function ImportSettings() {
    var self = this;
    var options = [];
    var createCsvMappingLink = $('#createCsvMappingLink');
    var createCsvJobForImportProperties = $('#createCsvJobForImportProperties');

    var selected = $('select[id*="_importSettings_importType"]>option:selected').val();
    $('select[id*="_importSettings_importType"]>option').each(function (i, el) {
        options.push({"value" :$(el).val(), "text" : $(el).text()});
    });

    self.init = function () {
        $('input[name*="[importSettings][source]"]').change(function () {
            self.reloadImportType($(this).val());
            self.hideControls($(this).val());
        });
        self.reloadImportType($('input[name*="[importSettings][source]"]:checked').val());
        self.hideControls($('input[name*="[importSettings][source]"]:checked').val());
    };

    self.reloadImportType = function (sourceType) {
        $('select[id$="_importSettings_importType"]>option').remove();
        $.each(options, function(i, el){
            if (sourceType == 'csv' || (sourceType == 'integrated_api' && el.value == 'multi_properties')) {
                $('select[id$="_importSettings_importType"]').append(
                    '<option value="' + el.value + '"' + (selected == el.value ? ' selected="selected"' : '' ) +'>' +
                            el.text +
                    '</option>'
                );
            }
        });
    };

    self.hideControls = function (sourceType) {
        if (sourceType == 'csv') {
            $('[id*="csv"]').parent().parent().show();
            $('[id*="api"]').parent().parent().hide();
            createCsvMappingLink.show();
            createCsvJobForImportProperties.show();
        } else if (sourceType == 'integrated_api') {
            $('[id*="csv"]').parent().parent().hide();
            $('[id*="api"]').parent().parent().show();
            createCsvMappingLink.hide();
            createCsvJobForImportProperties.hide();
        }
    }
}

$( document ).ready(function() {
    var importSettings = new ImportSettings();
    importSettings.init();
});
