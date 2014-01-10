function ContractDetails() {
  var self = this;
  this.propertiesList = ko.observableArray([]);
  this.unitsList = ko.observableArray([]);
  this.currentPropertyId = ko.observable();
  this.currentUnitId = ko.observable();
  this.contract = ko.observable();
  this.approve = ko.observable(false);
  this.review = ko.observable(false);
  this.edit = ko.observable(false);
  this.invite = ko.observable(false);
  this.due = ko.observableArray(['1th', '5th', '10th', '15th', '20th', '25th']);
  this.errorsApprove = ko.observableArray([]);
  this.errorsEdit = ko.observableArray([]);
  this.statusBeforeTriedSave = ko.observable();

  this.cancelEdit = function(data)
  {
    $('#tenant-edit-property-popup').dialog('close');
    if(self.approve()) {
      self.approveContract(self.contract());
    }
    self.clearDetails();
  };

  this.getUnits = function(propertyId) {
      self.unitsList([]);
      $('#unit-edit').parent().find('.loader').show();
      $.ajax({
          url: Routing.generate('landlord_units_list'),
          type: 'POST',
          dataType: 'json',
          data: {'property_id': propertyId },
          success: function(response) {
              $('#unit-edit').parent().find('.loader').hide();
              self.unitsList(response.units);
          }
      });
  };

  this.onPropertyChange = function(property, event) {
     if (self.currentPropertyId() != undefined) {
         self.getUnits(self.currentPropertyId());
     }
  };

  this.getProperties = function (propertyId) {
     self.propertiesList([]);
     $('#property-edit').parent().find('.loader').show();
     $.ajax({
         url: Routing.generate('landlord_properties_list_all'),
         type: 'POST',
         dataType: 'json',
         success: function (response) {
             $('#property-edit').parent().find('.loader').hide();
             self.propertiesList(response);
         }
     });
  };

  this.closeApprove = function(data) {
      $('#tenant-approve-property-popup').dialog('close');
      return false;
  }

  this.editContract = function(contract) {
    self.errorsApprove([]);
    self.errorsEdit([]);
    $('#unit-edit').html(' ');
    $('#tenant-approve-property-popup').dialog('close');
    $('#tenant-edit-property-popup').dialog('open');

    if (contract.first_name) {
      self.contract(contract);
    }

    self.currentPropertyId(self.contract().property_id);
    self.getProperties(self.contract().property_id);
    self.currentUnitId(self.contract().unit_id);
    self.getUnits(self.contract().property_id);


    var flag = false;
    if(self.approve()) {
      flag = true;
    }
    self.clearDetails();
    self.edit(true);
    self.approve(flag);
    window.jQuery.curCSS = window.jQuery.css;
    $('#contractEditStart').datepicker({
      showOn: "both",
      buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png", 
      format:'m/d/Y',
      minDate: 0,
      starts: 1,
      position: 'r',
      onBeforeShow: function(){
        $('#contractEditStart').DatePickerSetDate($('#contract-edit-start').val(), true);
      },
      onChange: function(formated, dates){
        $('#contractEditStart').val(formated);
        $('#contractEditStart').DatePickerHide();
      }
    });
    $('#contractEditFinish').datepicker({
      showOn: "both",
      buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
      format:'m/d/Y',
      minDate: 0,
      starts: 1,
      position: 'r',
      onBeforeShow: function(){
         $('#contractEditFinish').DatePickerSetDate($('#contract-edit-finish').val(), true);
      },
      onChange: function(formated, dates){
        $('#contractEditFinish').val(formated);
        $('#contractEditFinish').DatePickerHide();
      }
    });
  };
  this.approveContract = function(contract) {
    self.contract(contract);
    self.errorsApprove([]);
    self.errorsEdit([]);
    $('#unit-edit').html(' ');
    $('#tenant-approve-property-popup').dialog('open');
    self.clearDetails();

    self.approve(true);
    $('#contractApproveStart').attr('readonly', true);
    $('#contractApproveFinish').attr('readonly', true);

    if ($('#contractApproveStart').val().length > 0) {
        var start = $('#contractApproveStart').val();
    } else {
        var today = new Date();
        var start = today.toString('MM/dd/yyyy');
    }

    if ($('#contractApproveFinish').val().length > 0) {
        var finish = $('#contractApproveFinish').val();
    } else {
        var today = new Date();
        today.setFullYear(today.getFullYear()+1);
        var finish = today.toString('MM/dd/yyyy');
    }

    contract.start = start;
    contract.finish = finish;
    self.contract(contract);

    $('#contractApproveStart').datepicker({
      showOn: "both",
      buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png", 
      format:'m/d/Y',
      starts: 1,
      minDate: 0,
      position: 'r',
      onChange: function(formated, dates){
        $('#contractApproveStart').val(formated);
        $('#contractApproveStart').DatePickerHide();
      }
    });
    $('#contractApproveFinish').datepicker({
      showOn: "both",
      buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
      format:'m/d/Y',
      starts: 1,
      minDate: 0,
      position: 'r',
      onChange: function(formated, dates){
        $('#contractApproveFinish').val(formated);
        $('#contractApproveFinish').DatePickerHide();
      }
    });
  };

  this.countErrorsEdit = ko.computed(function(){
    return parseInt(self.errorsEdit().length);
  });

  this.countErrorsApprove = ko.computed(function(){
    return parseInt(self.errorsApprove().length);
  });

  this.reviewContract = function(data) {
    $('#unit-edit').html(' ');
    $('#tenant-review-property-popup').dialog('open');
    self.clearDetails();
    self.approve(false);
    self.contract(data);
    self.review(true);
  };
  this.approveSave = function() {
    var data = self.contract();
    self.statusBeforeTriedSave(data.status);
    data.status = 'approved';
    self.contract(data);
    self.saveContract();
  };
  this.removeTenant = function() {
    var data = self.contract();
    data.action = 'remove';
    self.contract(data);
    self.saveContract();
  };
  this.clearDetails = function(){
    self.edit(false);
    self.review(false);
    self.approve(false);
  };
  this.saveContract = function(){
    if (self.edit()) {
        var id = '#tenant-edit-property-popup';
    } else {
        var id = '#tenant-approve-property-popup';
    }

    jQuery(id).showOverlay();
    var contract = self.contract();

    if (self.currentUnitId() != undefined) {
        contract.unit_id = self.currentUnitId();
    }

    if (self.currentPropertyId() != undefined) {
        contract.property_id = self.currentPropertyId();
    }

    self.contract(contract);
    $.ajax({
      url: Routing.generate('landlord_contract_save'),
      type: 'POST',
      dataType: 'json',
      data: {
        'contract': self.contract()
      },
      success: function(response) {
        jQuery(id).hideOverlay();
        self.errorsApprove([]);
        self.errorsEdit([]);
        if (typeof response.errors == 'undefined') {
          $('#tenant-edit-property-popup').dialog('close');
          $('#tenant-approve-property-popup').dialog('close');
          self.clearDetails();
          ContractsViewModel.ajaxAction();
        } else {
          if (self.edit()) {
              self.editContract(self.contract());
              self.errorsEdit(response.errors);
          } else {
              if (self.contract().status == 'approved') {
                  self.contract().status = self.statusBeforeTriedSave();
                  self.approveContract(self.contract());
              }
              self.errorsApprove(response.errors);
          }
        }
      }
    });
  };
  this.revokeInvitation = function() {
      jQuery('#tenant-revoke-invotation').showOverlay();
      $.ajax({
          url: Routing.generate('revoke_invitation', {'contractId': self.contract().id }),
          type: 'GET',
          dataType: 'json',
          success: function(response) {
              jQuery('#tenant-revoke-invotation').hideOverlay();
              if (typeof response.error !== 'undefined') {
                  $('#tenant-review-property-popup').find('.error').html(response.error);
                  $('#tenant-review-property-popup').find('.error').show();
              } else {
                  $('#tenant-review-property-popup').find('.error').hide();
                  $('#tenant-revoke-invotation').dialog('close');
                  self.clearDetails();
                  ContractsViewModel.ajaxAction();
              }
          }
      });
  };
  this.closeRevokeInvitation = function() {
      $('#tenant-edit-property-popup').dialog('open');
      $('#tenant-revoke-invotation').dialog('close');
      return false;
  }

  this.closeReminderRevoke = function() {
    $('#tenant-edit-property-popup').dialog('close');
    $('#tenant-revoke-invotation').dialog('open');
    return false;
  }
  this.closeTenantReviewPropertyPopup = function() {
      $('#tenant-review-property-popup').dialog( 'close' );
      return false;
  }
  this.sendReminderInvition = function() {
     jQuery('#tenant-edit-property-popup').showOverlay();
     $.ajax({
        url: Routing.generate('send_reminder_invitation', {'contractId': self.contract().id }),
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            jQuery('#tenant-review-property-popup').hideOverlay();
            if (typeof response.error !== 'undefined') {
                self.errorsEdit.push(response.error);
            } else {
                self.errorsEdit([]);
            }
            jQuery('#tenant-edit-property-popup').hideOverlay();
        }
     });
  };
}

function Contracts() {
  var limit = 10;
  var current = 1;
  var self = this;
  this.aContracts = ko.observableArray([]);
  this.pages = ko.observableArray([]);
  this.total = ko.observable(0);
  this.current = ko.observable(1);
  this.sort = ko.observable('ASC');
  this.sortColumn = ko.observable("status");
  this.isSortAsc = ko.observable(true);
  this.searchText = ko.observable("");
  this.searchCollum = ko.observable("");
  this.isSearch = ko.observable(false);
  this.notHaveResult = ko.observable(false);
  this.processLoading = ko.observable(true);

  this.search = function() {
    var searchCollum = $('#searchFilter').linkselect('val');
    if(typeof searchCollum != 'string') {
       searchCollum = '';
    }
    if(searchCollum != 'status') {
      if(self.searchText().length <= 0) {
        $('#searsh-field-payments').css('border-color', 'red');
        return;
      } else {
        $('#searsh-field-payments').css('border-color', '#bdbdbd');
      }
    } else {
      var searchText = $('#searchPaymentsStatus').linkselect('val');
      if(typeof searchText != 'string') {
         searchText = '';
      }
      self.searchText(searchText);
    }
    self.isSearch(true);
    self.searchText(self.searchText());
    self.searchCollum(searchCollum);
    self.current(1);
    self.ajaxAction();
  };

  this.clearSearch = function() {
    self.searchText('');
    self.searchCollum('');
    self.current(1);
    self.ajaxAction();
    self.isSearch(false);
  };

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
    $('.content-box').show();
    self.aContracts([]);
    self.notHaveResult(false);
    self.processLoading(true);
    $.ajax({
      url: Routing.generate('landlord_contracts_list'),
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
        self.processLoading(false);
        self.aContracts([]);
        self.aContracts(response.contracts);

        self.total(response.total);
        self.pages(response.pagination);
        if(self.countContracts() <= 0) {
           self.notHaveResult(true);
        } else {
           self.notHaveResult(false);
        }
        if(self.sortColumn().length == 0) {
          return;
        }
        if(self.isSortAsc()) {
          $('#'+self.sortColumn()).attr('class', 'sort-dn');
        } else {
          $('#'+self.sortColumn()).attr('class', 'sort-up');
        }

        $('#'+self.sortColumn()).find('i').show();
        $.each($('.properties-table .sort i'), function( index, value ) {
           $(this).hide();
        });
      }
    });
  };

  this.countContracts = ko.computed(function(){
    return parseInt(self.aContracts().length);
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
  this.editContract = function(data) {
    var position = $('#edit-' + data.id).position();
    data.top = position.top - 300;
    DetailsViewModel.editContract(data);
  };
  this.approveContract = function(data) {
    var position = $('#edit-' + data.id).position();
    DetailsViewModel.approveContract(data);
  };
  this.reviewContract = function(data) {
    var position = $('#edit-' + data.id).position();
    DetailsViewModel.reviewContract(data);
  };
  this.addTenant = function() {
    $('#tenant-add-property-popup').dialog('open');
      if ($('.payment-end').val().length > 0) {
          var finish = $('.payment-end').val();
      } else {
          var today = new Date();
          today.setFullYear(today.getFullYear()+1);
          var finish = today.toString('MM/dd/yyyy');
      }
      if ($('.payment-start').val().length > 0) {
          var start = $('.payment-start').val();
      } else {
          var today = new Date();
          var start = today.toString('MM/dd/yyyy');
      }
      $('.payment-end').val(finish);
      $('.payment-start').val(start);
      $('.payment-start').attr('readonly', true);
      $('.payment-end').attr('readonly', true);
      $('.payment-start, .payment-end').datepicker({
      showOn: "both",
      buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
      dateFormat:'m/d/yy',
      minDate: 0
    });
  };
  this.filterAddress = function(data) {
    //console.log(data.id);
  };
}

var ContractsViewModel = new Contracts();
var DetailsViewModel = new ContractDetails();

$(document).ready(function(){

  var idProperty = '#rentjeeves_landlordbundle_invitetenantcontracttype_contract_property';
  var idUnit = '#rentjeeves_landlordbundle_invitetenantcontracttype_contract_unit';

  ko.applyBindings(ContractsViewModel, $('#contracts-block').get(0));
  ko.applyBindings(DetailsViewModel, $('#contract-actions').get(0));
  $('#tenant-approve-property-popup').dialog({
      position: ["center", 200],
      autoOpen: false,
      resizable: false,
      modal: true,
      width:'520px'
  });
  $('#tenant-edit-property-popup').dialog({
      position: "center",
      autoOpen: false,
      resizable: false,
      modal: true,
      width:'520px'
  });

  $('#tenant-review-property-popup').dialog({
      position: "center",
      autoOpen: false,
      resizable: false,
      modal: true,
      width:'520px'
  });

  $('#tenant-revoke-invotation').dialog({
      position: "center",
      autoOpen: false,
      resizable: false,
      modal: true,
      width:'520px'
  });

  $('#tenant-add-property-popup').dialog({
      position: "center",
      autoOpen: false,
      resizable: false,
      modal: true,
      width:'520px'
  });
  ContractsViewModel.ajaxAction();
  $('#searchFilter').linkselect("destroy");
  $('#searchFilter').linkselect({
    change: function(li, value, text){
      ContractsViewModel.searchText('');
      if(value == 'status') {
        $('#searchSelect').show();
        $('#searchInput').hide();
      } else {
        $('#searchSelect').hide();
        $('#searchInput').show();
      }
    }
  });
  
  $('#tenant-add-property-button-cancel').click(function(){
    $('#tenant-add-property-popup').dialog( 'close' );
    return false;
  });
  function getUnits(propertyId)
  {
      $(idUnit).linkselect('destroy');
      $(idUnit).html(' ');
      $(idUnit).linkselect();
      $.ajax({
        url: Routing.generate('landlord_units_list'),
        type: 'POST',
        dataType: 'json',
        data: {'property_id': propertyId},
        success: function(response) {

            if(response.units.length <= 0) {
              return;
            }

            var html = '';
            $.each(response.units, function(index, value) {
               var id = $(this).get(0).id;
               var name = $(this).get(0).name;
               var option = '<option value="'+id+'">'+name+'</option>';
               html += option;
            });

            $(idUnit).linkselect('destroy');
            $(idUnit).html(html);
            $(idUnit).linkselect();
        }
      });
  }

  $(idProperty).linkselect('destroy');
  $(idProperty).linkselect({
    change: function(li, value, text){
      getUnits(value);
    }
  });

  getUnits($(idProperty).linkselect('val'));

  $('#rentjeeves_landlordbundle_invitetenantcontracttype_tenant_email').change(function () {
      $.ajax({
        url: Routing.generate('landlord_check_email'),
        type: 'POST',
        dataType: 'json',
        data: {'email': $(this).val() },
        success: function(response) {
           if (response.userExist) {
              if(response.isTenant == false) {
                $('.userInfo').hide();
                $('#userExistMessageLanlord').show();
              } else {
                $('.userInfo').hide();
                $('#userExistMessage').show();
                $.each($('.userInfo').find('input'), function(index, value) {
                  var val = $.trim($(this).val());
                  if (val.length <= 0) {
                    $(this).val('');
                  }
                });
              }
           } else {
              $('.userInfo').show();
              $('.messageInfoUserAdd').hide();
              $.each($('.userInfo').find('input'), function(index, value) {
                  var val = $.trim($(this).val());
                  if (val.length <= 0) {
                    $(this).val('');
                  }
              });
           }
        }
      });
  });
  
  $('#rentjeeves_landlordbundle_invitetenantcontracttype').submit(function() {
    if($('#userExistMessageLanlord').is(':visible')) {
      return false;
    }
    return true;
  });
});
