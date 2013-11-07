// Include the UserVoice JavaScript SDK (only needed once on a page)
var user = {};
$(document).ready(function(){
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
  UserVoice.push(['identify', {
    email:      user.email, // User’s email address
    name:       user.fullName, // User’s real name
    created_at: user.login, // Unix timestamp for the date the user signed up
    id:         user.id, // Optional: Unique id of the user (if set, this should not change)
    type:       user.type // Optional: segment your users by type
//    account: {
//      id:           123, // Optional: associate multiple users with a single account
//      name:         'Acme, Co.', // Account name
//      created_at:   1364406966, // Unix timestamp for the date the account was created
//      monthly_rate: 9.99, // Decimal; monthly rate of the account
//      ltv:          1495.00, // Decimal; lifetime value of the account
//      plan:         'Enhanced' // Plan name for the account
//    }
  }]);
  UserVoice.push(['addTrigger', {
    mode: 'contact',
    trigger_position: 'top-right',
    trigger_color: 'white',
    trigger_background_color: '#458dd6',
    accent_color: '#458dd6'
  }]);
  
  UserVoice.push('autoprompt', {});
  console.log(user);
});