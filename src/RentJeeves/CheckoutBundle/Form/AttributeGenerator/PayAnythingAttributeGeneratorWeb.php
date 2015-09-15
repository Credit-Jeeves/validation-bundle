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
