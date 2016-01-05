<?php

namespace RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper;

use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\Exception\InvalidAttributeNameException;

class PaymentAccount
{
    /**
     * @var object
     */
    protected $entity;

    /**
     * @var array
     */
    protected $attrs = [];

    /**
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param object $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @param string $attributeName
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
     * @param string $attributeName
     * @param mixed $attributeValue
     * @return $this
     */
    public function set($attributeName, $attributeValue)
    {
        $this->attrs[$attributeName] = $attributeValue;

        return $this;
    }

    /**
     * @param string $attributeName
     * @return bool
     */
    public function has($attributeName)
    {
        return array_key_exists($attributeName, $this->attrs);
    }
}
