$(document).ready(function () {
    $('button.next').click(function () {
        var form = $('form');

        if (form.is('#questions') === true) {
            if (checkFillAllQuestions() == false) {
                return false;
            }
        }

        form.parent().showOverlay();

        return true;
    });
});

/**
 * Checks whether there are unanswered questions.
 * If such questions exist - show message with their numbers and return FALSE
 * else
 * return TRUE
 *
 * @return boolean
 */
function checkFillAllQuestions() {
    var questionsDiv = $('div#questions>div');
    var countQuestionsWithoutAnswer = 0;

    questionsDiv.each(function () {
        if ($(this).find('input:radio:checked').length === 0) {
            countQuestionsWithoutAnswer++;
        }
    });

    if (countQuestionsWithoutAnswer > 0) {
        var message = Translator.trans('pidkiq.error.unanswered_questions', { COUNT: countQuestionsWithoutAnswer });
        var errorBox = $('div.attention-box');
        if (errorBox.length > 0) {
            errorBox.html(message);
        } else {
            $('form#questions').prepend('<div class="attention-box pie-el">' + message + '</div>');
        }

        return false;
    }

    return true;
}
