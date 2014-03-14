var accountingImport = new accountingImport();

$(document).ready(function() {
    ko.applyBindings(accountingImport, $('#reviewContainer').get(0));
    accountingImport.loadData();
    $('.submitImportFile').click(function(){
        accountingImport.loadData();
        return false;
    })
});
