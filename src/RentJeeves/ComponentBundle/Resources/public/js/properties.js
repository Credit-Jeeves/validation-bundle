function Properties() {
  var limit = 10;
  var current = 1;
  var self = this;
  this.processProperty = ko.observable(false);
  this.aProperties = ko.observableArray([]);
  this.pages = ko.observableArray([]);
  this.total = ko.observable(0);
  this.current = ko.observable(1);
  this.sortColumn = ko.observable("number");
  this.isSortAsc = ko.observable(true);
  this.searchText = ko.observable("");
  this.searchCollum = ko.observable("");

  this.sortFunction = function(data, event) {
     field = event.target.id;
     if(field.length == 0) {
        return;
     }
     $('.sort').each(function() {
      $(this).show();
      $('#'+self.sortColumn()).find('.sortUp').removeClass('sortUpOnly');
     });
     self.sortColumn(field);
     if(self.isSortAsc() == true) {
        self.isSortAsc(false);
        $('#'+self.sortColumn()).find('.sortUp').hide();
     } else {
        self.isSortAsc(true);
        $('#'+self.sortColumn()).find('.sortDown').hide();
        $('#'+self.sortColumn()).find('.sortUp').addClass('sortUpOnly');
     }
     self.current(1);
     self.ajaxAction();
  };
  this.ajaxAction = function() {
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
        self.total(response.total);
        self.pages(response.pagination);
        self.processProperty(false);
        if(self.sortColumn().length == 0) {
          return;
        }
        if(self.isSortAsc() == true) {
          $('#'+self.sortColumn()).find('.sortUp').addClass('sortUpOnly');
          $('#'+self.sortColumn()).find('.sortDown').hide();
        } else {
          $('#'+self.sortColumn()).find('.sortUp').hide();
        }
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
  
  this.ajaxAction = function(nPropertyId) {
    self.property(nPropertyId);
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
  this.deleteProperty = function() {
    if (confirm('Are you sure?')) {
      if (confirm('Are you really sure?')) {
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
      }
    }
  };
}

function Search() {
  this.searchText = ko.observable("");
  this.searchCollum = ko.observable("");
  this.property = ko.observable("");
  this.isSearch =  ko.observable(false);
  var self = this;

  this.searchFunction = function() {
    console.info('hello from search');
    var searchCollum = $('#searchFilterSelect').linkselect('val');
    if(typeof searchCollum != 'string') {
       searchCollum = '';
    }
    if(self.searchText().length <= 0) {
      return;
    }

    self.property().searchText(self.searchText());
    self.property().searchCollum(searchCollum);
    self.property().current(1);
    self.property().ajaxAction();
    self.isSearch(true);
  }

  this.clearSearch = function() {
    self.property().searchText('');
    self.property().searchCollum('');
    self.property().current(1);
    self.property().ajaxAction();
    self.isSearch(false);
  }
}

var PropertiesViewModel = new Properties();
var UnitsViewModel = new Units();
var search = new Search();
search.property(PropertiesViewModel);

$(document).ready(function(){
  ko.applyBindings(PropertiesViewModel, $('#properties-block').get(0));
  PropertiesViewModel.ajaxAction();
  ko.applyBindings(UnitsViewModel, $('#units-block').get(0));
  ko.applyBindings(search, $('#searchContent').get(0));
  $('#searchFilterSelect').linkselect("destroy");
  $('#searchFilterSelect').linkselect();
});
