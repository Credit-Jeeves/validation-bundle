$(document).ready(function(){

    function isChecked(el) {
        if (el.is(':checkbox') && el.is(':checked')) {
            return true;
        } else if (!el.is(':checkbox') && 1 == el.val()) {
            return true;
        }
        return false;
    }

    $('.checkbox-anywhere').each(function() {
        var el = $(this).find('input');
        el.hide();
        if (isChecked(el)) {
            $(this).addClass('checkbox-on');
        } else {
            $(this).addClass('checkbox-off');
        }
    });

    $('.checkbox-anywhere').click(function() {
        var el = $(this).find('input');
        if (isChecked(el)) {
            if (el.is(':checkbox')) {
                el.prop('checked', false);
            } else {
                el.val(0);
            }
            $(this).removeClass('checkbox-on');
            $(this).addClass('checkbox-off');
        } else {
            if (el.is(':checkbox')) {
                el.prop('checked', true);
            } else {
                el.val(1);
            }
            $(this).removeClass('checkbox-off');
            $(this).addClass('checkbox-on');
        }
    });
});
