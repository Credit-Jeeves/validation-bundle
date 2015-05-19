<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;

interface AttributeGeneratorInterface
{
    public function isMobile();

    public function amountAttrs();

    public function paidForAttrs();

    public function amountOtherAttrs();

    public function totalAttrs();

    public function typeAttrs();

    public function frequencyAttrs();

    public function dueDateAttrs();

    public function startMonthAttrs();

    public function startYearAttrs();

    public function startDateAttrs();

    public function endsAttrs();

    public function endMonthAttrs();

    public function endYearAttrs();

    public function paymentAccountIdAttrs();

    public function contractIdAttrs();

    public function idAttrs();

    public function submitAttrs();

    public function paymentAccountAttrs();
}
