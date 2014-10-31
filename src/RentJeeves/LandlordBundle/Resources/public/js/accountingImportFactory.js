
eval("var accountingImportChild = new "+className+"()");
var accountingImport = new accountingImport(accountingImportChild);

$(document).ready(function() {
    extend(accountingImportChild, accountingImport);
    ko.applyBindings(accountingImportChild, $('#reviewContainer').get(0));
    accountingImportChild.isMultipleProperty(isMultipleProperty);
    accountingImportChild.loadData(false);
});
