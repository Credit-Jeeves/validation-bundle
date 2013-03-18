  var LightBox = {
  show: function(sLink){
    $('#lightbox-content').empty();
     $('<iframe>', {src: sLink})
          .addClass('lightbox-iframe')
          .appendTo($('#lightbox-content'));
    this.setScreen();
    $('#lightbox-background').show();
    $('#lightbox-container').fadeIn();
  },
  display: function(dContent, nWidth, nHeight){
    nWidth = nWidth || 500;
    nHeight = nHeight || 500;
    $('#lightbox-container').css({width: nWidth, height: nHeight});
    $('#lightbox-content').empty();
    this.setScreen();
    $('#lightbox-content').html(dContent);
    $('#lightbox-background').show();
    $('#lightbox-container').fadeIn();
  },
  hide: function(){
    $('body').css('overflow', 'auto');
    $('#lightbox-container').fadeToggle("500", function(){
      $('#lightbox-background').hide();
    });
  },
  setScreen: function(){
    $('body').css('overflow', 'hidden');
    this.resize();
  },
  resize: function(){
    nMarginLeft = Math.floor(($(window).width() - $('#lightbox-container').outerWidth())/2);
    nMarginTop = Math.floor(($(window).height() - $('#lightbox-container').outerHeight())/2);
    $('#lightbox-container').css({'margin-left': nMarginLeft, 'margin-top': nMarginTop});
  },
  };
  $(document).ready(function(){  
    $(window).resize(function(){
      LightBox.resize();
    });
//    $('#simulation-container .lightbox-anywhere').live('click', function(event){
//    event.preventDefault();
//    var sLink = $(this).attr('href') || 'http://creditjeeves.uservoice.com/knowledgebase';
//    if (sLink == '#') {
//      sLink = 'http://creditjeeves.uservoice.com/knowledgebase';
//    }
//    LightBox.show(sLink);
//  });
  $('.lightbox-anywhere').click(function(event){
    event.preventDefault();
    var sLink = $(this).attr('href') || 'http://creditjeeves.uservoice.com/knowledgebase';
    if (sLink == '#') {
      sLink = 'http://creditjeeves.uservoice.com/knowledgebase';
    }
    LightBox.show(sLink);
  });
  $('.lightbox-close').click(function(){
    LightBox.hide();
  });
});
