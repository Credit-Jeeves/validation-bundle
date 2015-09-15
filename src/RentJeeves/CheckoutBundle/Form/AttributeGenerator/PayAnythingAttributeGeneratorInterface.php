<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;

interface PayAnythingAttributeGeneratorInterface extends AttributeGeneratorInterface
{
    /**
     * @return array
     */
    public static function payForAttrs();
}
