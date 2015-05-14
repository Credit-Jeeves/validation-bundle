<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;

class AttributeGeneratorWeb extends AttributeGenerator implements AttributeGeneratorInterface
{
    public function isMobile()
    {
        return false;
    }
    public function amountAttrs()
    {
        return array_merge(
            parent::amountAttrs(),
            array(
                'class' => 'half-of-right',
                'data-bind' => 'value: payment.amount',
            )
        );
    }

    public function paidForAttrs()
    {
        return array_merge(
            parent::paidForAttrs(),
            array(
                'class' => 'original paid-for',
                'data-bind' => "options: payment.paidForOptions, optionsText: 'text', optionsValue: 'value', ".
                    "value: payment.paidFor",
                'force_row' => false,
                'template' => 'paidFor-html'
            )
        );
    }

    public function amountOtherAttrs()
    {
        return array_merge(
            parent::amountOtherAttrs(),
            array(
                'class' => 'half-of-right',
                'data-bind' => 'value: payment.amountOther'
            )
        );
    }

    public function totalAttrs()
    {
        return array_merge(
            parent::totalAttrs(),
            array(
                'data-bind' => 'value: totalInput',
                'view' => array(
                    'data-bind' => 'text: getTotal',
                )
            )
        );
    }

    public function typeAttrs()
    {
        return array_merge(
            parent::typeAttrs(),
            array(
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
                'row_attr' => array(
                    'data-bind' => ''
                )
            )
        );
    }

    public function frequencyAttrs()
    {
        return array_merge(
            parent::frequencyAttrs(),
            array(
                'class' => 'original',
                'data-bind' => 'value: payment.frequency',
                'row_attr' => array(
                    'data-bind' => 'visible: \'recurring\' == payment.type()'
                )
            )
        );
    }

    public function dueDateAttrs()
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

    public function startMonthAttrs()
    {
        return array_merge(
            parent::startMonthAttrs(),
            array(
                'class' => 'original',
                'data-bind' => '
                            options: payment.startMonths,
                            value: payment.startMonth,
                            optionsCaption: "",
                            optionsText: "name",
                            optionsValue: "number"
                            ',
                'row_attr' => array(
                    'data-bind' => 'visible: \'recurring\' == payment.type()'
                )
            )
        );
    }

    public function startYearAttrs()
    {
        return array_merge(
            parent::startYearAttrs(),
            array(
                'class' => 'original',
                'data-bind' => '
                            options: payment.startYears,
                            value: payment.startYear,
                            optionsCaption: "",
                            optionsText: "name",
                            optionsValue: "number"
                            ',
                'row_attr' => array(
                    'data-bind' => 'visible: \'recurring\' == payment.type()'
                )
            )
        );
    }

    public function startDateAttrs()
    {
        return array_merge(
            parent::startDateAttrs(),
            array(
                'class' => 'datepicker-field',
                'row_attr'  => array(
                    'data-bind' => 'visible: \'one_time\' == payment.type()
                                || contract.groupSetting.pay_balance_only'
                ),
                'data-bind' => 'datepicker: payment.startDate, ' .
                    'datepickerOptions: { minDate: new Date(), dateFormat: \'m/d/yy\', beforeShowDay: isDueDay }',
            )
        );
    }

    public function endsAttrs()
    {
        return array_merge(
            parent::endsAttrs(),
            array(
                'data-bind' => 'checked: payment.ends',
                'row_attr' => array(
                    'data-bind' => 'visible: \'recurring\' == payment.type()'
                )
            )
        );
    }

    public function endMonthAttrs()
    {
        return array_merge(
            parent::endMonthAttrs(),
            array(
                'class' => 'original',
                'data-bind' => 'value: payment.endMonth, enable: \'on\' == payment.ends()',
                'box_attr' => array(
                    'data-bind' => 'visible: \'on\' == payment.ends()'
                )
            )
        );
    }

    public function endYearAttrs()
    {
        return array_merge(
            parent::endYearAttrs(),
            array(
                'class' => 'original',
                'data-bind' => 'value: payment.endYear, enable: \'on\' == payment.ends()',
                'box_attr' => array(
                    'data-bind' => 'visible: \'on\' == payment.ends()'
                )
            )
        );
    }

    public function paymentAccountIdAttrs()
    {
        return array_merge(
            parent::paymentAccountIdAttrs(),
            array(
                'data-bind' => 'value: payment.paymentAccountId',
            )
        );
    }

    public function contractIdAttrs()
    {
        return array_merge(
            parent::contractIdAttrs(),
            array(
                'data-bind' => 'value: payment.contractId',
            )
        );
    }

    public function idAttrs()
    {
        return array_merge(
            parent::idAttrs(),
            array(
                'data-bind' => 'value: payment.id',
            )
        );
    }

    public function submitAttrs()
    {
        return array_merge(
            parent::submitAttrs(),
            array(
                'force_row' => true, 'class' => 'hide_submit'
            )
        );
    }

    public function paymentAccountAttrs()
    {
        return array_merge(
            parent::paymentAccountAttrs(),
            array(
                'style'=>'display:none'
            )
        );
    }

}
