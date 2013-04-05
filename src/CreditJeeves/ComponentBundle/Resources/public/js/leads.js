$(document).ready(function(){
  $('#lead-select-button').click(function(){
    LightBox.display($('#lead-select-form'));
  });

//  $('#dealer-select').click(function(){
//    LightBox.display($('#lead-select-form'));
//  });

  $('#lightbox-content').delegate('.lead-select-lead', 'click', function(){
      var nLeadId = this.id.split('-')[1];
      jQuery.ajax({
        url: 'lead',
        type: 'POST',
        data: {'lead_id': nLeadId},
        dataType: 'json',
        success: function () {
          location.reload();
        },
        complete: function () {
        }
    });
      
  });
});