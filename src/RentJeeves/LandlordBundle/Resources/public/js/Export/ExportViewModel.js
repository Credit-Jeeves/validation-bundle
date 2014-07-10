function ExportViewModel() {
    var self = this;

    this.exportTypes = ko.observableArray([
//        {type: 'xml', typeName: Translator.trans('base.order.report.type.yardi')},
//        {type: 'csv', typeName: Translator.trans('base.order.report.type.realpage')},
//        {type: 'promas', typeName: Translator.trans('base.order.report.type.promas')}
    ]);
    this.selectedType = ko.observable(null);
    this.properties = ko.observableArray([]);
    this.selectedProperty = ko.observable(null);
    this.propertyId = ko.observable(null);
    this.accountId = ko.observable(null);
    this.arAccountId = ko.observable(null);
    this.begin = ko.observable(null);
    this.end = ko.observable(null);
    this.zipBatch = ko.observable(false);

}
