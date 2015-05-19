<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;

abstract class AttributeGenerator implements AttributeGeneratorInterface
{
    public function amountAttrs()
    {
        return  [
            'min' => 1,
            'step' => '0.01'
        ];
    }

    public function paidForAttrs()
    {
        return [];
    }

    public function amountOtherAttrs()
    {
        return [
            'step' => '0.01'
        ];
    }

    public function totalAttrs()
    {
        return [
            'view' => []
        ];
    }

    public function typeAttrs()
    {
        return [];
    }

    public function frequencyAttrs()
    {
        return [];
    }

    public function dueDateAttrs()
    {
        return [];
    }

    public function startMonthAttrs()
    {
        return [];
    }

    public function startYearAttrs()
    {
        return [];
    }

    public function startDateAttrs()
    {
        return [];
    }

    public function endsAttrs()
    {
        return [];
    }

    public function endMonthAttrs()
    {
        return [];
    }

    public function endYearAttrs()
    {
        return [];
    }

    public function paymentAccountIdAttrs()
    {
        return [];
    }

    public function contractIdAttrs()
    {
        return [];
    }

    public function idAttrs()
    {
        return [];
    }

    public function submitAttrs()
    {
        return [];
    }

    public function paymentAccountAttrs()
    {
        return [];
    }
}
