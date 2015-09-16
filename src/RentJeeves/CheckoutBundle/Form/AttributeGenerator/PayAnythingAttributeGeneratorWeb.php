<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;

class PayAnythingAttributeGeneratorWeb extends AttributeGeneratorWeb implements PayAnythingAttributeGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public static function payForAttrs()
    {
        return [
            'class' => 'original',
            'html' =>
                // green message box for empty pay_for
                '<div class="tooltip-box type4 pie-el" data-bind="visible: !payFor()">' .
                '<h4 data-bind="text: Translator.trans(\'checkout.pay_anything.choose_payFor.title\')"></h4>' .
                '<p data-bind="text: Translator.trans(\'checkout.pay_anything.choose_payFor.text\')"></p>' .
                '</div>' .
                // green message box for pay_for
                '<div class="tooltip-box type4 pie-el" data-bind="visible: payFor()">' .
                '<h4 data-bind="i18n: {\'PAY_FOR\': payForText}">' .
                'checkout.pay_anything.pay_for.tooltip.title-%PAY_FOR%' .
                '</h4>' .
                '<p data-bind="i18n: {\'PAY_FOR\': payForText}">' .
                'checkout.pay_anything.pay_for.tooltip.text-%PAY_FOR%' .
                '</p></div>',
            'data-bind' => '
                        options: availablePayFor,
                        value: payFor,
                        optionsCaption: "",
                        optionsText: "name",
                        optionsValue: "value"
                        ',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function startDateAttrs($isPastCutoffTime = false)
    {
        $minDate = $isPastCutoffTime ? new \DateTime('+1 day') : new \DateTime();

        return [
            'class' => 'datepicker-field',
            'html' =>
                // green message box for empty start_date
                '<div class="tooltip-box type4 pie-el" data-bind="visible: !payment.startDate()">' .
                '<h4 data-bind="text: Translator.trans(\'checkout.payment.choose_date.title\')"></h4>' .
                '<p data-bind="text: Translator.trans(\'checkout.payment.choose_date.text\')"></p>' .
                '</div>' .
                // green message box for start_date
                '<div class="tooltip-box type4 pie-el" data-bind="visible: payment.startDate()">'.
                '<h4 data-bind="i18n: {\'START\': payment.startDate, \'SETTLE\': settle}">' .
                'checkout.one_time.tooltip.title-%START%-%SETTLE%' .
                '</h4>' .
                '<p data-bind="i18n: {\'AMOUNT\': getTotal, \'START\': payment.startDate}">' .
                'checkout.one_time.tooltip.text-%AMOUNT%-%START%' .
                '</p></div>',
            'data-bind' => 'datepicker: payment.startDate, ' .
                'datepickerOptions: {
                    minDate: \'' . $minDate->format('m/d/Y') . '\',
                    dateFormat: \'m/d/yy\',
                    beforeShowDay: isDueDay
                }',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function dueDateAttrs()
    {
        return [
            'data-bind' => 'value: payment.dueDate'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function startMonthAttrs()
    {
        return [
            'data-bind' => 'value: payment.startMonth'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function startYearAttrs()
    {
        return [
            'data-bind' => 'value: payment.startYear'
        ];
    }
}
