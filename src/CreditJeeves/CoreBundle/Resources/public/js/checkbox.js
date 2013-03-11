$(document).ready(function(){
  $('.checkbox-anywhere').each(function(){
    if ($(this).find('input').val() == 1) {
      $(this).addClass('checkbox-on');
    } else {
      $(this).addClass('checkbox-off');
    }
    
  });

  $('.checkbox-anywhere').click(function(){
    if ($(this).find('input').val() == 1) {
      $(this).removeClass('checkbox-on');
      $(this).find('input').val(0);
      $(this).addClass('checkbox-off');
    } else {
      $(this).removeClass('checkbox-off');
      $(this).addClass('checkbox-on');
      $(this).find('input').val(1);
    }
  });
});