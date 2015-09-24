<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;

class AttributeGeneratorWeb extends AttributeGenerator
{
    /**
     * {@inheritdoc}
     */
    public static function isMobile()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public static function amountAttrs()
    {
        return array_merge(
            parent::amountAttrs(),
            [
                'class' => 'half-of-right',
                'data-bind' => 'value: payment.amount',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function paidForAttrs()
    {
        return array_merge(
            parent::paidForAttrs(),
            [
                'class' => 'original paid-for',
                'data-bind' => "options: payment.paidForOptions, optionsText: 'text', optionsValue: 'value', ".
                    "value: payment.paidFor",
                'force_row' => false,
                'template' => 'paidFor-html'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function amountOtherAttrs()
    {
        return array_merge(
            parent::amountOtherAttrs(),
            [
                'class' => 'half-of-right',
                'data-bind' => 'value: payment.amountOther'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function totalAttrs()
    {
        return array_merge(
            parent::totalAttrs(),
            [
                'data-bind' => 'value: totalInput',
                'view' => [
                    'data-bind' => 'text: getTotal',
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function typeAttrs()
    {
        return array_merge(
            parent::typeAttrs(),
            [
                'class' => 'original',
                'html' =>
                // green message box for recurring payment
                    '<div class="tooltip-box type3 pie-el" ' .
                    'data-bind="visible: (\'recurring\' == payment.type() && !!payment.startDate())">' .
                    '<h4 data-bind="' .
                    'text: \'checkout.recurring.\' + payment.frequency() + \'.tooltip.title-%DUE_DAY%\', ' .
                    'i18n: {\'DUE_DAY\': payment.dueDate}' .
                    '"></h4>' .
                    '<p data-bind="' .
                    'text: \'checkout.recurring.\' + payment.frequency() + \'.\' + payment.ends() + ' .
                    '\'.tooltip.text-%AMOUNT%-%DUE_DAY%-%ENDS_ON%-%SETTLE_DAYS%\', ' .
                    'i18n: {' .
                    '\'AMOUNT\': getTotal, ' .
                    '\'DUE_DAY\': payment.dueDate, ' .
                    '\'SETTLE_DAYS\': settleDays, ' .
                    '\'ENDS_ON\': getLastPaymentDay' .
                    '}' .
                    '"></p></div>' .
                    // green message box for empty start_date
                    '<div class="tooltip-box type3 pie-el" data-bind="visible: !payment.startDate()">' .
                    '<h4 data-bind="text: Translator.trans(\'checkout.payment.choose_date.title\')"></h4>' .
                    '<p data-bind="text: Translator.trans(\'checkout.payment.choose_date.text\')"></p>' .
                    '</div>' .
                    // green message box for one_time payment
                    '<div class="tooltip-box type3 pie-el" ' .
                    'data-bind="visible: (\'one_time\' == payment.type() && !!payment.startDate())">'.
                    '<h4 data-bind="i18n: {\'START\': payment.startDate, \'SETTLE\': settle}">' .
                    'checkout.one_time.tooltip.title-%START%-%SETTLE%' .
                    '</h4>' .
                    '<p data-bind="i18n: {\'AMOUNT\': getTotal, \'START\': payment.startDate}">' .
                    'checkout.one_time.tooltip.text-%AMOUNT%-%START%' .
                    '</p></div>',
                'data-bind' => 'value: payment.type',
                'row_attr' => [
                    'data-bind' => ''
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function frequencyAttrs()
    {
        return array_merge(
            parent::frequencyAttrs(),
            [
                'class' => 'original',
                'data-bind' => 'value: payment.frequency',
                'row_attr' => [
                    'data-bind' => 'visible: \'recurring\' == payment.type()'
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function dueDateAttrs()
    {
        return array_merge(
            parent::dueDateAttrs(),
            array(
                'class' => 'original',
                'data-bind' => "options: payment.dueDates," .
                    "value: payment.dueDate, optionsCaption: ''",
                'box_attr' => array(
                    'data-bind' => 'visible: \'monthly\' == payment.frequency()'
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function startMonthAttrs()
    {
        return array_merge(
            parent::startMonthAttrs(),
            [
                'class' => 'original',
                'data-bind' => '
                            options: payment.startMonths,
                            value: payment.startMonth,
                            optionsCaption: "",
                            optionsText: "name",
                            optionsValue: "number"
                            ',
                'row_attr' => [
                    'data-bind' => 'visible: \'recurring\' == payment.type()'
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function startYearAttrs()
    {
        return array_merge(
            parent::startYearAttrs(),
            [
                'class' => 'original',
                'data-bind' => '
                            options: payment.startYears,
                            value: payment.startYear,
                            optionsCaption: "",
                            optionsText: "name",
                            optionsValue: "number"
                            ',
                'row_attr' => [
                    'data-bind' => 'visible: \'recurring\' == payment.type()'
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function startDateAttrs($isPastCutoffTime = false)
    {
        $minDate = $isPastCutoffTime ? new \DateTime('+1 day') : new \DateTime();

        return array_merge(
            parent::startDateAttrs($isPastCutoffTime),
            [
                'class' => 'datepicker-field',
                'row_attr' => [
                    'data-bind' => 'visible: \'one_time\' == payment.type()
                                || contract().groupSetting.pay_balance_only'
                ],
                'data-bind' => 'datepicker: payment.startDate, ' .
                    'datepickerOptions: {
                        minDate: \'' . $minDate->format('m/d/Y') . '\',
                        dateFormat: \'m/d/yy\',
                        beforeShowDay: isDueDay
                    }',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function endsAttrs()
    {
        return array_merge(
            parent::endsAttrs(),
            [
                'data-bind' => 'checked: payment.ends',
                'row_attr' => [
                    'data-bind' => 'visible: \'recurring\' == payment.type()'
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function endMonthAttrs()
    {
        return array_merge(
            parent::endMonthAttrs(),
            [
                'class' => 'original',
                'data-bind' => 'value: payment.endMonth, enable: \'on\' == payment.ends()',
                'box_attr' => [
                    'data-bind' => 'visible: \'on\' == payment.ends()'
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function endYearAttrs()
    {
        return array_merge(
            parent::endYearAttrs(),
            [
                'class' => 'original',
                'data-bind' => 'value: payment.endYear, enable: \'on\' == payment.ends()',
                'box_attr' => [
                    'data-bind' => 'visible: \'on\' == payment.ends()'
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function paymentAccountIdAttrs()
    {
        return array_merge(
            parent::paymentAccountIdAttrs(),
            [
                'data-bind' => 'value: payment.paymentAccountId',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function contractIdAttrs()
    {
        return array_merge(
            parent::contractIdAttrs(),
            [
                'data-bind' => 'value: payment.contractId',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function idAttrs()
    {
        return array_merge(
            parent::idAttrs(),
            [
                'data-bind' => 'value: payment.id',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function submitAttrs()
    {
        return array_merge(
            parent::submitAttrs(),
            [
                'force_row' => true, 'class' => 'hide_submit'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function paymentAccountAttrs()
    {
        return array_merge(
            parent::paymentAccountAttrs(),
            [
                'style' => 'display:none'
            ]
        );
    }
}
