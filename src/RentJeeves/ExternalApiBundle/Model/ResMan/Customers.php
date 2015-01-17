<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

class Customers
{
    /**
     * @Serializer\SerializedName("Customer")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\Customer")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlElement(namespace="http://www.w3.org/2005/Atom")
     */
    protected $customer;

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }
}
