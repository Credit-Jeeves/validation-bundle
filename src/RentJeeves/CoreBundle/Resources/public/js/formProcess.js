window.formProcess = {
    applyErrorsToField: function(formName, fieldName, errors) {
        var formValidator = jsfv[formName];
        jQuery.each(errors, function(key, error) {
            if ('[object Array]' === Object.prototype.toString.call(error)) {
                window.formProcess.applyErrorsToField(formName, key, error);
            } else {
                var field = formValidator.id(fieldName);
                if (field) {
                    formValidator.addError(field, error);
                } else {
                    formValidator.addError(formValidator.id(formName), error);
                }
            }
        });
    },

    applyErrors: function(data) {
        jQuery.each(data, function(formName, fields) {
            jQuery.each(fields, function(fieldName, errors) {
                window.formProcess.applyErrorsToField(formName, fieldName, errors);
            });
        });
    },

    removeAllErrors: function(popupId) {
        $(popupId + ' .attention-box').hide();
        $(popupId + ' .attention-box ul li').remove();
        $(popupId + ' .error').removeClass('error');
    },

    addFormError: function(field, errorMessage) {
        var attentionBox = $(field).parents('.attention-container').find('.attention-box');
        attentionBox.show();

        // Add errors block
        $(field).parents('.form-row').addClass('error');
        $(field).addClass('error');

        // Add error
        attentionBox.find('ul').append('<li>'+errorMessage+'</li>');
    },

    reLogin: function(jqXHR, errorThrown) {
        if ('SyntaxError: Unexpected token <' == errorThrown &&
            '<!DOCTYPE' == jqXHR.responseText.substr(0, 9)
            ) {
            jQuery('body').showOverlay();
            window.location.reload();
        }
    }
};
