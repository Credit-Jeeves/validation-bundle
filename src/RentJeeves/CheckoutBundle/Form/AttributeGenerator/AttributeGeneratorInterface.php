<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;


interface AttributeGeneratorInterface {

    function amountAttrs();

    function paidForAttrs();

    function amountOtherAttrs();

    function totalAttrs();

    function typeAttrs();

    function frequencyAttrs();

    function dueDateAttrs();

    function startMonthAttrs();

    function startYearAttrs();

    function startDateAttrs();

    function endsAttrs();

    function endMonthAttrs();

    function endYearAttrs();

    function paymentAccountIdAttrs();

    function contractIdAttrs();

    function idAttrs();

    function submitAttrs();

    function paymentAccountAttrs();

    # add more here

}
