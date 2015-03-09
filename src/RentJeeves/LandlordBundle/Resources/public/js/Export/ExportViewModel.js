function ExportViewModel() {
    this.exportTypes = ko.observableArray([]);
    this.selectedType = ko.observable(null);
    this.properties = ko.observableArray([]);
    this.selectedProperty = ko.observable(null);
    this.propertyId = ko.observable(null);
    this.buildingId = ko.observable(null);
    this.accountId = ko.observable(null);
    this.arAccountId = ko.observable(null);
    this.begin = ko.observable(null);
    this.end = ko.observable(null);
    this.makeZip = ko.observable(false);
    this.includeAllGroups = ko.observable(false);
    this.exportBy = ko.observable();
}
