<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;

abstract class AttributeGenerator implements AttributeGeneratorInterface
{
    public function amountAttrs()
    {
        return  array(
            'min' => 1,
            'step' => '0.01'
        );
    }

    public function paidForAttrs()
    {
        return array(

        );

    }

    public function amountOtherAttrs()
    {
        return array(
            'step' => '0.01'
        );

    }

    public function totalAttrs()
    {
        return array(
            'view' => array()
        );
    }

    public function typeAttrs()
    {
        return array(

        );

    }

    public function frequencyAttrs()
    {
        return array(

        );
    }

    public function dueDateAttrs()
    {
        return array(

        );
    }

    public function startMonthAttrs()
    {
        return array(

        );
    }

    public function startYearAttrs()
    {
        return array(

        );
    }

    public function startDateAttrs()
    {
        return array(

        );
    }

    public function endsAttrs()
    {
        return array(

        );
    }

    public function endMonthAttrs()
    {
        return array(

        );
    }

    public function endYearAttrs()
    {
        return array(

        );
    }

    public function paymentAccountIdAttrs()
    {
        return array(

        );
    }

    public function contractIdAttrs()
    {
        return array(

        );
    }

    public function idAttrs()
    {
        return array(

        );
    }

    public function submitAttrs()
    {
        return array();
    }

    public function paymentAccountAttrs()
    {
        return array();
    }
}
