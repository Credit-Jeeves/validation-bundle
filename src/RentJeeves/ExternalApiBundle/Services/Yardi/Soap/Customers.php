<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class Customers
{
    /**
     * @Serializer\SerializedName("Customer")
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Customer>")
     * @Serializer\XmlList(inline = true, entry = "Customer")
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
