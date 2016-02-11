$( document ).ready(function() {
    $.reject({
        reject: {
            msie: 10,
            safari: 5
        },
        imagePath: '/bundles/user/img/',
        closeCookie: true,
        // Header of pop-up window
        header: Translator.trans('reject.header'),
        // Paragraph 1
        paragraph1: Translator.trans('reject.paragraph1'),
        // Paragraph 2
        paragraph2: Translator.trans('reject.paragraph2'),
        close: true, // Allow closing of window
        // Message displayed below closing link
        closeMessage: Translator.trans('reject.closeMessage'),
        closeLink: Translator.trans('reject.closeWindow')
    });
});
