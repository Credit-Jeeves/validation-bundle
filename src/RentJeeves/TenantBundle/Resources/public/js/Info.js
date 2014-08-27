function Info() {
    this.popup = ko.observable(false);
    this.openPopup = function () {
        $('#reporting-popup').dialog('open');
    };
}

$(document).ready(function () {
    ko.applyBindings(new Info(), $('#info-block').get(0));
    $('#reporting-popup').dialog({
        autoOpen: false,
        modal: true,
        width: '520px'
    });
});
