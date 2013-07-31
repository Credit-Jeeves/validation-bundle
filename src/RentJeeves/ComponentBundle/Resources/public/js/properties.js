function Properties() {
  var limit = 10;
  var current = 1;
  var self = this;
  this.processProperty = ko.observable(false);
  this.aProperties = ko.observableArray([]);
  this.pages = ko.observableArray([]);
  this.total = ko.observable(0);
  this.current = ko.observable(1);
  self.sortColumn = ko.observable("");
  self.isSortAsc = ko.observable(true);
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
     this.ajaxAction();
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
          'isSortAsc': self.isSortAsc()
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

var PropertiesViewModel = new Properties();
var UnitsViewModel = new Units();

$(document).ready(function(){
  ko.applyBindings(PropertiesViewModel, $('#properties-block').get(0));
  PropertiesViewModel.ajaxAction();
  ko.applyBindings(UnitsViewModel, $('#units-block').get(0));
});
