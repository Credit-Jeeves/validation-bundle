var id = 0;
var tenant = '';
var address = '';
$(document).ready(function(){
  $('.reporting-action').click(function(){
    id = this.id.split('-')[1];
    tenant = $('#tenant-' + id).html();
    address = $('#address-' + id).html();
    var action = this.title;
    if ('start' == action) {
      var text = 'By signing bellow, I ' + tenant + ', am opting in to have my rent payments for ' + address + ' reported to Experian';
      $('#reporting-agreement').html(text);
      $('#reporting-start').dialog('open');
    } else {
      var text = 'Rent payments for ' + address + ' are currently being reported to major credit service agencies. Once eligible payments have been reported to the credit reporting agencies, they cannot be erased. Furthermore, stopping and subsequently starting reporting again may have a negative on your credit history.';
      $('#reporting-alert').html(text);
      $('#reporting-stop').dialog('open');
    }
  });
  $('#close-start').click(function(){
    $('#reporting-start').dialog('close');
  });
  $('#close-stop').click(function(){
    $('#reporting-stop').dialog('close');
  });
  $('#stop-reporting').click(function(){
    $.ajax({
      url: Routing.generate('tenant_contract_reporting'),
      type: 'POST',
      dataType: 'json',
      data: {
          'contract_id' : id,
          'action' : 'stop'
      },
      success: function() {
        $('#reporting-stop').dialog('close');
        $('#action-' + id).attr('title', 'start');
        $('#action-' + id).html('Start Reporting');
      }
    });
  });
  $('#start-reporting').click(function(){
    $.ajax({
      url: Routing.generate('tenant_contract_reporting'),
      type: 'POST',
      dataType: 'json',
      data: {
          'contract_id' : id,
          'action' : 'start'
      },
      success: function() {
        $('#reporting-start').dialog('close');
        $('#action-' + id).attr('title', 'stop');
        $('#action-' + id).html('Stop Reporting');
      }
    });
  });
  $('#reporting-start').dialog({ 
    autoOpen: false,
    resizable: false,
    modal: true,
    width:'520px'
  });
  $('#reporting-stop').dialog({ 
    autoOpen: false,
    resizable: false,
    modal: true,
    width:'520px'
  });
});