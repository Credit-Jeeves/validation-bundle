<?php

namespace RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper;

use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\Exception\InvalidAttributeNameException;

class PaymentAccount
{
    protected $entity;

    protected $attrs = [];

    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @param $attributeName
     * @return mixed
     * @throws InvalidAttributeNameException
     */
    public function get($attributeName)
    {
        if (array_key_exists($attributeName, $this->attrs)) {
            return $this->attrs[$attributeName];
        }

        throw new InvalidAttributeNameException(sprintf('Attribute "%s" is invalid', $attributeName));
    }

    /**
     * @param $attributeName
     * @param $attributeValue
     * @return $this
     */
    public function set($attributeName, $attributeValue)
    {
        $this->attrs[$attributeName] = $attributeValue;

        return $this;
    }
}
