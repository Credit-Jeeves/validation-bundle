<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;

interface AttributeGeneratorInterface
{
    /**
     * @return bool
     */
    public static function isMobile();

    /**
     * @return array
     */
    public static function amountAttrs();

    /**
     * @return array
     */
    public static function paidForAttrs();

    /**
     * @return array
     */
    public static function amountOtherAttrs();

    /**
     * @return array
     */
    public static function totalAttrs();

    /**
     * @return array
     */
    public static function typeAttrs();

    /**
     * @return array
     */
    public static function frequencyAttrs();

    /**
     * @return array
     */
    public static function dueDateAttrs();

    /**
     * @return array
     */
    public static function startMonthAttrs();

    /**
     * @return array
     */
    public static function startYearAttrs();

    /**
     * @param bool $isPastCutoffTime
     * @param null $minDate
     * @return array
     */
    public static function startDateAttrs($isPastCutoffTime = false, $minDate = null);

    /**
     * @return array
     */
    public static function endsAttrs();

    /**
     * @return array
     */
    public static function endMonthAttrs();

    /**
     * @return array
     */
    public static function endYearAttrs();

    /**
     * @return array
     */
    public static function paymentAccountIdAttrs();

    /**
     * @return array
     */
    public static function contractIdAttrs();

    /**
     * @return array
     */
    public static function idAttrs();

    /**
     * @return array
     */
    public static function submitAttrs();

    /**
     * @return array
     */
    public static function paymentAccountAttrs();
}
