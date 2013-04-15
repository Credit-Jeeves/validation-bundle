(function($){
  $.fn.ssn = function() {
    var aSsn = [];
    var aSsnIds = [];
    var nKey = 0;
     // This is hide/show block
     $(this).each(function() {
       var id = $(this).attr('id');
       aSsn[id] = nKey;
       aSsnIds.push('ssn_' + id);
       var clone = $(this).clone().attr('id', 'ssn_' + id);
       $(this).hide();
       clone.show().attr('type', 'password');
       clone.insertAfter($(this));
       $(clone).bind('focus', function(){
         // Show original
         $('#' + id).show();
         $('#' + id).focus();
         $(this).hide();
       });
       $('#' + id).bind('blur', function(event) {
         // Show mask
         var ssn = $(this).val();
         clone.val(ssn);
         $(this).hide();
         clone.show();
       });
       $('#' + id).bind('keyup', function(){
         var nSsn = $(this).val().length;
         // move cursor forward
         if (nSsn == $(this).attr('maxlength')) {
           nSsn = aSsn[$(this).attr('id')];
           if ( nSsn < 2) {
             $(this).blur();
             $('#' + aSsnIds[nSsn + 1]).focus();
           }
         }
         // move cursor back
         if (nSsn == 0) {
           nSsn = aSsn[$(this).attr('id')];
           if (nSsn > 0) {
             $(this).blur();
             $('#' + aSsnIds[nSsn - 1]).focus().val($('#' + aSsnIds[nSsn - 1]).val());;
           }
         }
       });
       nKey++;
     });
   };
}) (jQuery);

