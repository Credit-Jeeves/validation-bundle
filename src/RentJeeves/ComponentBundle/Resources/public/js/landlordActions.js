//FIXME refactor. Move it to template. Replace

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
    $('.datepicker').datepicker({
        showOn: "both",
        buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
        buttonImageOnly: true,
        showOtherMonths: true,
        selectOtherMonths: true,
        dateFormat: 'm/d/yy',
        minDate: new Date()
    });
});
