<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;

abstract class AttributeGenerator implements AttributeGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public static function amountAttrs()
    {
        return  [
            'min' => 1,
            'step' => '0.01'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function paidForAttrs()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function amountOtherAttrs()
    {
        return [
            'step' => '0.01'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function totalAttrs()
    {
        return [
            'view' => []
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function typeAttrs()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function frequencyAttrs()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function dueDateAttrs()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function startMonthAttrs()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function startYearAttrs()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function startDateAttrs($isPastCutoffTime = false, $minDate = null)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function endsAttrs()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function endMonthAttrs()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function endYearAttrs()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function paymentAccountIdAttrs()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function contractIdAttrs()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function idAttrs()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function submitAttrs()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function paymentAccountAttrs()
    {
        return [];
    }
}
