//@TODO fix this global var
function markAsNotValid()
{
    $('#saveProperty').addClass('grey');
    $('#addUnitToNewProperty').addClass('grey');
}

function markAsValid()
{
    $('#saveProperty').removeClass('grey');
    $('#addUnitToNewProperty').removeClass('grey');
}

var PropertiesViewModel = new properties();
var UnitsViewModel = new Units();
var search = new searchProperties();
var addProperties = new addProperties();
var removeProperty = new removeProperty();

$(document).ready(function(){

    search.property(PropertiesViewModel);
    addProperties.property(PropertiesViewModel);

    $('#add-property-popup').dialog({
        autoOpen: false,
        resizable: false,
        modal: true,
        width:'520px'
    });
    $('#remove-property-popup').dialog({
        autoOpen: false,
        resizable: false,
        modal: true,
        width:'520px'
    });
    $('#edit-property-popup').dialog({
        autoOpen: false,
        resizable: false,
        modal: true,
        width:'520px'
    });

    ko.applyBindings(PropertiesViewModel, $('#properties-block').get(0));
    PropertiesViewModel.ajaxAction();
    ko.applyBindings(search, $('#searchContent').get(0));
    ko.applyBindings(UnitsViewModel, $('#edit-property-popup').get(0));
    ko.applyBindings(addProperties, $('#add-property-popup').get(0));
    ko.applyBindings(removeProperty, $('#remove-property-popup').get(0));
    $('#searchFilterSelect').linkselect("destroy");
    $('#searchFilterSelect').linkselect();

    $('.property-button-add').click(function(){
        $('#add-property-popup').dialog('open');
        return false;
    });

    var google = $('#property-search').google({
        formId: null,
        findButtonId: "search-submit",
        findInputId: "property-search",
        mapCanvasId: 'search-result-map',
        loadingSpinner: true,
        loadingSpinnerClass: 'loadingSpinner',
        autoHideLoadingSpinner: true,
        addPropertyCallbackNotValid: function(jqXHR, errorThrown, textStatus)
        {
            markAsNotValid();
        },
        clearSearchCallback: function(isEmpty)
        {
            if (isEmpty) {
                markAsNotValid();
            }
        },
        markers: false,
        divIdError: false,
        defaultLat: null,
        defaultLong: null,
        clearSearchId: null,
        clearSearchClass: 'clearSearchProperty',
        divIdError: 'errorSearch',
        addPropertyCallback: function(data, textStatus, jqXHR)
        {
            markAsValid();
        },
        changeSearch: function(){
            markAsNotValid();
        }
    });

    addProperties.google(google);
});