$(document).ready(function(){
  $('.contract-delete').click(function(){
    var address = $(this).find('input').val();
    $('#contract-address').html(address);
    $('#contract-delete').dialog('open');
    return false;
  });
  $('#button-cancel').click(function(){
    $('#contract-delete').dialog('close');
  });
//  $('#rentjeeves_landlordbundle_invitetenantcontracttype_tenant_email').change(function () {
//    $.ajax({
//      url: Routing.generate('landlord_check_email'),
//      type: 'POST',
//      dataType: 'json',
//      data: {'email': $(this).val() },
//      success: function(response) {
//         if (response.userExist) {
//            if(response.isTenant == false) {
//              $('.userInfo').hide();
//              $('#userExistMessageLanlord').show();
//            } else {
//              $('.userInfo').hide();
//              $('#userExistMessage').show();
//              $.each($('.userInfo').find('input'), function(index, value) {
//                var val = $.trim($(this).val());
//                if (val.length <= 0) {
//                  $(this).val('none');
//                }
//              });
//            }
//         } else {
//            $('.userInfo').show();
//            $('.messageInfoUserAdd').hide();
//            $.each($('.userInfo').find('input'), function(index, value) {
//                var val = $.trim($(this).val());
//                if (val.length <= 0) {
//                  $(this).val('none');
//                }
//            });
//         }
//      }
//    });
//});  
  $('#contract-delete').dialog({ 
    autoOpen: false,
    resizable: false,
    modal: true,
    width:'520px'
  });
});