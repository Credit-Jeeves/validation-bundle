<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;


class AttributeGeneratorWeb extends AttributeGenerator implements AttributeGeneratorInterface{

    function amountAttrs()
    {
        $attributes = parent::amountAttrs();
        $attributes[] = array(
            'class' => 'half-of-right',
            'data-bind' => 'value: payment.amount',
        );

        return $attributes;
    }

    function paidForAttrs()
    {
        $attributes = parent::paidForAttrs();
        $attributes[] = array(
            'class' => 'original paid-for',
            'data-bind' => "options: payment.paidForOptions, optionsText: 'text', optionsValue: 'value', ".
                "value: payment.paidFor",
            'force_row' => false,
            'template' => 'paidFor-html'
        );

        return $attributes;

    }

    function amountOtherAttrs()
    {
        $attributes = parent::amountOtherAttrs();
        $attributes[] = array(
            'class' => 'half-of-right',
            'data-bind' => 'value: payment.amountOther'
        );

        return $attributes;

    }

    function totalAttrs()
    {
        $attributes = parent::totalAttrs();
        $attributes[] = array(
            'data-bind' => 'value: totalInput',
            'view' => array(
                'data-bind' => 'text: getTotal',
            )
        );

        return $attributes;
    }

    function typeAttrs()
    {
        $attributes = parent::typeAttrs();
        $attributes[] = array(
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
        );

        return $attributes;
    }

    function frequencyAttrs()
    {
        $attributes = parent::frequencyAttrs();
        $attributes[] = array(
            'class' => 'original',
            'data-bind' => 'value: payment.frequency',
            'row_attr' => array(
                'data-bind' => 'visible: \'recurring\' == payment.type()'
            )
        );
        return $attributes;
    }

    function dueDateAttrs()
    {
        $attributes = parent::dueDateAttrs();
        $attributes[] = array(
            'class' => 'original',
            'data-bind' => "options: payment.dueDates," .
                "value: payment.dueDate, optionsCaption: ''",
            'box_attr' => array(
                'data-bind' => 'visible: \'monthly\' == payment.frequency()'
            )
        );

        return $attributes;
    }

    function startMonthAttrs()
    {
        $attributes = parent::startMonthAttrs();
        $attributes[] = array(
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
        );

        return $attributes;
    }

    function startYearAttrs()
    {
        $attributes = parent::startYearAttrs();
        $attributes[] = array(
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
        );

        return $attributes;
    }

    function startDateAttrs()
    {
        $attributes = parent::startDateAttrs();
        $attributes[] = array(
            'class' => 'datepicker-field',
            'row_attr'  => array(
                'data-bind' => 'visible: \'one_time\' == payment.type()
                            || contract.groupSetting.pay_balance_only'
            ),
            'data-bind' => 'datepicker: payment.startDate, ' .
                'datepickerOptions: { minDate: new Date(), dateFormat: \'m/d/yy\', beforeShowDay: isDueDay }',
        );

        return $attributes;
    }

    function endsAttrs()
    {
        $attributes = parent::endsAttrs();
        $attributes[] = array(
            'data-bind' => 'checked: payment.ends',
            'row_attr' => array(
                'data-bind' => 'visible: \'recurring\' == payment.type()'
            )
        );

        return $attributes;
    }

    function endMonthAttrs()
    {
        $attributes = parent::endMonthAttrs();
        $attributes[] = array(
            'class' => 'original',
            'data-bind' => 'value: payment.endMonth, enable: \'on\' == payment.ends()',
            'box_attr' => array(
                'data-bind' => 'visible: \'on\' == payment.ends()'
            )
        );

        return $attributes;
    }

    function endYearAttrs()
    {
        $attributes = parent::endYearAttrs();
        $attributes[] = array(
            'class' => 'original',
            'data-bind' => 'value: payment.endYear, enable: \'on\' == payment.ends()',
            'box_attr' => array(
                'data-bind' => 'visible: \'on\' == payment.ends()'
            )
        );

        return $attributes;
    }

    function paymentAccountIdAttrs()
    {
        $attributes = parent::paymentAccountIdAttrs();
        $attributes[] = array(
            'data-bind' => 'value: payment.paymentAccountId',
        );

        return $attributes;
    }

    function contractIdAttrs()
    {
        $attributes = parent::contractIdAttrs();
        $attributes[] = array(
            'data-bind' => 'value: payment.contractId',
        );

        return $attributes;
    }

    function idAttrs()
    {
        $attributes = parent::idAttrs();
        $attributes[] = array(
            'data-bind' => 'value: payment.id',
        );

        return $attributes;
    }

    function submitAttrs(){
        $attributes = parent::submitAttrs();
        $attributes[] = array('force_row' => true, 'class' => 'hide_submit');
        return $attributes;
    }

    function paymentAccountAttrs(){
        $attributes = parent::paymentAccountAttrs();
        $attributes[] = array('style'=>'display:none');
        return $attributes;
    }


}
