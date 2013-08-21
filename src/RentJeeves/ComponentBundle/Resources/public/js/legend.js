$(document).ready(function(){
  $('.tradeline-legend-link').click(function(){
    $('#legend-popup').show();
    return false;
  });
  $('#legend-close').click(function(){
    $('#legend-popup').hide();
  });
});