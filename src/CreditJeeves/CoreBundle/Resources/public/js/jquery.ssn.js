(function($) {
    $.fn.ssn = function() {
        var aSsn = [];
        var aSsnIds = [];
        var orSsnIds = [];
        var nKey = 0;
        // This is hide/show block
        $(this).each(function() {
            var id = $(this).attr('id');
            orSsnIds.push('#' + id);
            aSsn[id] = nKey;
            aSsnIds.push('#ssn_' + id);
            var clone = $('#ssn_' + id);
            if (!clone.length) {
                clone = $(this).clone();
                $(this).hide();
                clone.attr('type', 'password')
                    .attr('id', 'ssn_' + id)
                    .removeClass('user-ssn');
                clone.insertAfter($(this));
            }

            $(clone).bind('focus', function() {
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
            $('#' + id).bind('keydown', function(eventObject) {
                var nSsn = $(this).val().length;

                var char = String.fromCharCode(eventObject.keyCode);
                if (0 <= char && char <= 9) {
                    if (eventObject.target.selectionStart != eventObject.target.selectionEnd) {
                        return true;
                    }
                    // move cursor forward
                    if (nSsn == $(this).attr('maxlength')) {
                        nSsn = aSsn[$(this).attr('id')];
                        if (nSsn < 2) {
                            $(this).blur();
                            $(aSsnIds[nSsn + 1]).focus().select();
                            $(orSsnIds[nSsn + 1]).select();
                        }
                    }
                } else if (8 == eventObject.keyCode) {
                    // move cursor back
                    if (nSsn == 0) {
                        nSsn = aSsn[$(this).attr('id')];
                        if (nSsn > 0) {
                            $(this).blur();
                            $(aSsnIds[nSsn - 1]).focus().val($(aSsnIds[nSsn - 1]).val());
                        }
                    }
                } else {
                    return false;
                }
            });

//            $('#' + id).bind('keyup', function(eventObject) {
//                var nSsn = $(this).val().length;
//                if (nSsn == $(this).attr('maxlength')) {
//                    nSsn = aSsn[$(this).attr('id')];
//                    if (nSsn < 2) {
//                        $(this).blur();
//                        $(aSsnIds[nSsn + 1]).focus();
//                    }
//                }
//            });
            nKey++;
        });
    };
})(jQuery);

