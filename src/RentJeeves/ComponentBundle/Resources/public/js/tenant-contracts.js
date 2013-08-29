$(document).ready(function(){
  var id = 0;
  $('.contract-delete').click(function(){
    id = this.id.split('-')[1];
    var address = $(this).find('input').val();
    $('#contract-address').html(address);
    $('#contract-delete').dialog('open');
    return false;
  });
  $('#button-cancel').click(function(){
    $('#contract-delete').dialog('close');
  });
  $('#button-contract-delete').click(function(){
    $('#contract-delete').dialog('close');
    $('#contract-loader').dialog('open');
    $.ajax({
      url: Routing.generate('tenant_contract_delete'),
      type: 'POST',
      dataType: 'json',
      data: {'contract_id': id },
      success: function(response) {
        location.reload();
      }
    });
  });
  $('#contract-delete').dialog({ 
    autoOpen: false,
    resizable: false,
    modal: true,
    width:'520px'
  });
  $('#contract-loader').dialog({ 
    autoOpen: false,
    resizable: false,
    modal: true,
    width:'520px'
  });
  
});