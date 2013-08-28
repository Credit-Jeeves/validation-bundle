$(document).ready(function(){
  $('.tradeline-legend-link').click(function(){
    $('#legend-popup').dialog('open');
    return false;
  });
  $('#legend-close').click(function(){
    $('#legend-popup').dialog('close');
  });
  $('#legend-popup').dialog({ 
    autoOpen: false,
    resizable: false,
    modal: true,
    width:'520px'
  });
});