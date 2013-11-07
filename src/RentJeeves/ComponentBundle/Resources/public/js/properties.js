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

function Properties() {
  var limit = 10;
  var current = 1;
  var self = this;
  this.processProperty = ko.observable(true);
  this.aProperties = ko.observableArray([]);
  this.pages = ko.observableArray([]);
  this.total = ko.observable(0);
  this.current = ko.observable(1);
  this.sortColumn = ko.observable("number");
  this.isSortAsc = ko.observable(true);
  this.searchText = ko.observable("");
  this.searchCollum = ko.observable("");
  this.last = ko.observable('Last');
  this.sortFunction = function(data, event) {
     field = event.target.id;

     if(field.length == 0) {
        return;
     }
     self.sortColumn(field);
     $('.sort-dn').attr('class', 'sort');
     $('.sort-up').attr('class', 'sort');
     if(self.isSortAsc() === false) {
      self.isSortAsc(true);
      $('#'.field).attr('class', 'sort-dn');
     } else {
      self.isSortAsc(false);
      $('#'.field).attr('class', 'sort-up');
     }
     
     self.current(1);
     self.ajaxAction();
  };
  this.ajaxAction = function() {
    $('#properties-block').show();
    self.processProperty(true);
    $.ajax({
      url: Routing.generate('landlord_properties_list'),
      type: 'POST',
      dataType: 'json',
      data: {
        'data': {
          'page' : self.current(),
          'limit' : limit,
          'sortColumn': self.sortColumn(),
          'isSortAsc': self.isSortAsc(),
          'searchCollum': self.searchCollum(),
          'searchText': self.searchText(),
        }
      },
      success: function(response) {
        self.aProperties([]);
        self.aProperties(response.properties);
        if (self.aProperties().length <= 0 && self.searchText().length == 0) {
          return location.href = Routing.generate('landlord_property_new');
        }
        self.total(response.total);
        self.pages(response.pagination);
        self.processProperty(false);
        $('#all').html(self.total());
        if(self.sortColumn().length == 0) {
          return;
        }
        if(self.isSortAsc()) {
          $('#'+self.sortColumn()).attr('class', 'sort-dn');
        } else {
          $('#'+self.sortColumn()).attr('class', 'sort-up');
        }

        $('#'+self.sortColumn()).find('i').show();
        $.each($('.properties-table-block .sort i'), function( index, value ) {
           $(this).hide();
        });
      }
    });
  };
  this.editUnits = function(property){
    UnitsViewModel.ajaxAction(property.id);
  };
  this.countProperties = ko.computed(function(){
    return parseInt(self.aProperties().length);
  });
  this.goToPage = function(page) {
    self.current(page);
    if (page == 'First') {
      self.current(1);
    }
    if (page == 'Last') {
      self.current(Math.ceil(self.total()/limit));
    }
    self.ajaxAction();
  };
}

function Units() {
  var self = this;
  this.aUnits = ko.observableArray([]);
  this.total = ko.observable(1);
  this.add = ko.observable(1);
  this.property = ko.observable(0);
  this.show = ko.observable(false);
  this.name = ko.observable();
  this.process = ko.observable(true);
  this.ajaxAction = function(nPropertyId) {
    $('#edit-property-popup').dialog('open');
    self.property(nPropertyId);
    self.process(true);
    $.ajax({
      url: Routing.generate('landlord_units_list'),
      type: 'POST',
      dataType: 'json',
      data: {'property_id': nPropertyId},
      success: function(response) {
        self.name(response.property);
        self.aUnits(response.units);
        self.total(response.units.length);
        self.show(true);
        self.process(false);
      }
    });
  };
  this.clearUnits = function() {
    self.aUnits([]);
    self.total(0);
    self.add(1);
    self.property(0);
    self.show(false);
  };
  this.addUnits = function() {
    for(var i=0; i < self.add(); i++) {
      self.aUnits.push({'name': '', 'id': ''});
    }
    var count = parseInt(self.total());
    count += parseInt(self.add());
    self.total(count);
  };
  this.saveUnits = function() {
    $('#edit-property-popup').dialog('close');
    $.ajax({
      url: Routing.generate('landlord_units_save'),
      type: 'POST',
      dataType: 'json',
      data: {'units': self.aUnits(), 'property_id': self.property()},
      success: function(response) {
        self.clearUnits();
        PropertiesViewModel.ajaxAction();
      }
    });
  };
  this.removeUnit = function(unit) {
    if (confirm('Are you sure?')) {
      self.aUnits.remove(unit);
    }
  };
  this.deletePropertyConfirm = function()
  {
    $('#edit-property-popup').dialog('close');
    removeProperty.show();
  };

  this.deleteProperty = function() {
    $.ajax({
      url: Routing.generate('landlord_property_delete'),
      type: 'POST',
      dataType: 'json',
      data: {'property_id': self.property()},
      success: function(response) {
        self.clearUnits();
        PropertiesViewModel.ajaxAction();
      }
    });
  };
}

function Search() {
  this.searchText = ko.observable("");
  this.searchCollum = ko.observable("");
  this.property = ko.observable("");
  this.isSearch =  ko.observable(false);
  var self = this;

  this.searchFunction = function() {
    var searchCollum = $('#searchFilterSelect').linkselect('val');

    if(typeof searchCollum != 'string') {
       searchCollum = '';
    }
    if(self.searchText().length <= 0) {
      $('#search').css('border-color', 'red');
      return;
    } else {
      $('#search').css('border-color', '#bdbdbd');
    }
    self.isSearch(true);
    self.property().searchText(self.searchText());
    self.property().searchCollum(searchCollum);
    self.property().current(1);
    self.property().ajaxAction();
  };

  this.clearSearch = function() {
    self.property().searchText('');
    self.property().searchCollum('');
    self.property().current(1);
    self.property().ajaxAction();
    self.searchCollum('');
    self.searchText('');
    self.isSearch(false);
  };
}

function addProperties()
{
  this.property = ko.observable("");
  this.aUnits = ko.observableArray([]);
  this.add = ko.observable(1);
  this.google = ko.observable("");
  var self = this;
  this.clearUnits = function() {
    self.aUnits([]);
    self.add(1);
  };

  this.addUnits = function() {
    for(var i=0; i < self.add(); i++) {
      self.aUnits.push({'name': '', 'id': ''});
    }
  };

  this.saveUnits = function(propertyId) {
    $.ajax({
      url: Routing.generate('landlord_units_save'),
      type: 'POST',
      dataType: 'json',
      data: {'units': self.aUnits(), 'property_id': propertyId },
      success: function(response) {
        self.clearUnits();
        self.property().ajaxAction();
        $('#property-search').val(' ');
        markAsNotValid();
      }
    });
  };

  this.saveProperty = function() {
    if($('#saveProperty').hasClass("grey")) {
      return;
    }

    var place = self.google().place;
    var data = {'address': place.address_components, 'geometry':place.geometry};

    self.property().processProperty(true);
    $('#add-property-popup').dialog('close');

    jQuery.ajax({
        url: Routing.generate('landlord_property_add'),
        type: 'POST',
        dataType: 'json',
        data: {'data': JSON.stringify(data, null)},
        error: function(jqXHR, errorThrown, textStatus) {;
        },
        success: function(data, textStatus, jqXHR) {
            var propertyId = data.property.id;
            if(propertyId) {
                return self.saveUnits(propertyId);
            }

            alert('Something wrong, we can\'t save property');
        }
    });
  };

  this.removeUnit = function(unit) {
    if (confirm('Are you sure?')) {
      self.aUnits.remove(unit);
    }
  };
}

function removeProperty()
{
  var self = this;
  this.aUnits = ko.observableArray([]);
  this.name = ko.observable('gg');
  this.countUnit = ko.observable('333');
  this.show = function(){
    self.aUnits([]);
    $('#remove-property-popup').dialog('open');
    self.aUnits(UnitsViewModel.aUnits());
    self.name(UnitsViewModel.name());
    self.countUnit(UnitsViewModel.aUnits().length);
  };

  this.deleteProperty = function()
  {
    $('#remove-property-popup').dialog('close');
    UnitsViewModel.deleteProperty();
  };

  this.cancel = function()
  {
    $('#remove-property-popup').dialog('close');
    $('#edit-property-popup').dialog('open');
  };
}

var PropertiesViewModel = new Properties();
var UnitsViewModel = new Units();
var search = new Search();
var addProperties = new addProperties();
var removeProperty = new removeProperty();
search.property(PropertiesViewModel);
addProperties.property(PropertiesViewModel);

$(document).ready(function(){

    var ERROR = 'notfound';
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
