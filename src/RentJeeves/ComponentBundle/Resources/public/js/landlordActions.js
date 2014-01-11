var ActionsViewModel = new Actions();
var ResolveViewModel = new Resolve();

$(document).ready(function(){
    ko.applyBindings(ActionsViewModel, $('#actions-block').get(0));
    ko.applyBindings(ResolveViewModel, $('#contract-resolve').get(0));
    ActionsViewModel.ajaxAction();
    $('#contract-resolve-late').dialog({
        autoOpen: false,
        resizable: false,
        modal: true,
        width:'520px'
    });
    $('#contract-resolve-ended').dialog({
        autoOpen: false,
        resizable: false,
        modal: true,
        width:'520px'
    });
});
