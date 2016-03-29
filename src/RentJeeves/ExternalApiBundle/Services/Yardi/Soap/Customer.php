<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class Customer
{
    /**
     * It's resident id of User
     *
     * @Serializer\SerializedName("CustomerID")
     * @Serializer\Type("string")
     */
    protected $customerId;

    /**
     * address of Customer
     *
     * @Serializer\SerializedName("Address")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\CustomerAddress")
     */
    protected $customerAddress;

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    public function getResidentId()
    {
        $this->getCustomerId();
    }

    /**
     * @return CustomerAddress
     */
    public function getCustomerAddress()
    {
        return $this->customerAddress;
    }

    /**
     * @param CustomerAddress $customerAddress
     */
    public function setCustomerAddress($customerAddress)
    {
        $this->customerAddress = $customerAddress;
    }
}
