<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;


abstract class AttributeGenerator implements AttributeGeneratorInterface {

    function amountAttrs()
    {
        return  array(
            'min' => 1,
            'step' => '0.01'
        );
    }

    function paidForAttrs()
    {
        return array(

        );

    }

    function amountOtherAttrs()
    {
        return array(
            'step' => '0.01'
        );

    }

    function totalAttrs()
    {
        return array(
            'view' => array()
        );
    }

    function typeAttrs()
    {
        return array(

        );

    }

    function frequencyAttrs()
    {
        return array(

        );
    }

    function dueDateAttrs()
    {
        return array(

        );
    }

    function startMonthAttrs()
    {
        return array(

        );
    }

    function startYearAttrs()
    {
        return array(

        );
    }

    function startDateAttrs()
    {
        return array(

        );
    }

    function endsAttrs()
    {
        return array(

        );
    }

    function endMonthAttrs()
    {
        return array(

        );
    }

    function endYearAttrs()
    {
        return array(

        );
    }

    function paymentAccountIdAttrs()
    {
        return array(

        );
    }

    function contractIdAttrs()
    {
        return array(

        );
    }

    function idAttrs(){
        return array(

        );
    }

    function submitAttrs(){
        return array();
    }

    function paymentAccountAttrs(){
        return array();
    }


}
