$(document).ready(function(){
  $('#iframe-get-started').click(function(){
    window.open('http://www.renttrack.com/');
  });
  $('#rentjeeves_publicbundle_logintype_save').click(function(event){
    event.preventDefault();
    var data = $('#iframe-login-form form').serializeArray();
    $.ajax({
      url: Routing.generate('management_ajax_login'),
      type: 'POST',
      dataType: 'json',
      data: data,
      error: function(jqXHR, errorThrown, textStatus) {
          //nothing to do now
      },
      success: function(data, textStatus, jqXHR) {
        var redirect = data.url;
        if (redirect != '') {
          window.open(data.url);
        }
      }
    });
  });
});