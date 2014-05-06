function properties() {
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
          'searchText': self.searchText()
        }
      },
      success: function(response) {
        self.aProperties([]);
        self.aProperties(response.properties);
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
      $('#edit-property-popup').dialog('open');
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
    this.getUnitsText = function(property) {
        if (property.isSingle) {
            return Translator.trans('property.is_standalone');
        }
        return property.units;
    }

    this.removeSingleProperty = function(property) {
        removeProperty.showStandalone(property);
    }
}
