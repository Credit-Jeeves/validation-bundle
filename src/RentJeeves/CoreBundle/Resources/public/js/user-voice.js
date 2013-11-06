// Include the UserVoice JavaScript SDK (only needed once on a page)
UserVoice=window.UserVoice||[];
(function(){
  var uv=document.createElement('script');
  uv.type='text/javascript';
  uv.async=true;
  uv.src = ('https:' == document.location.protocol ? 'https://' : 'http://') +
  'widget.uservoice.com/INfNSELJGn5OTXDcoOCHxg.js';  
  var s=document.getElementsByTagName('script')[0];
  s.parentNode.insertBefore(uv,s);
})();

UserVoice.push(['set', {
  accent_color: '#e2753a',
  trigger_color: 'white',
  trigger_background_color: 'rgba(46, 49, 51, 0.6)'
  }]);

UserVoice.push(['addTrigger', {
  mode: 'contact', // Modes: contact (default), smartvote, satisfaction
  trigger_position: 'top-right',
  trigger_color: 'white',
  trigger_background_color: '#458dd6',
  accent_color: '#458dd6'
}]);

UserVoice.push('autoprompt', {});
