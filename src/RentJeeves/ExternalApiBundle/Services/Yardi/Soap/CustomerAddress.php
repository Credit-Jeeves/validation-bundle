<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class CustomerAddress
{
    /**
     * @Serializer\SerializedName("Address1")
     * @Serializer\Type("string")
     */
    protected $customerAddress1;

    /**
     * @return mixed
     */
    public function getCustomerAddress1()
    {
        return $this->customerAddress1;
    }

    /**
     * @param mixed $customerAddress1
     */
    public function setCustomerAddress1($customerAddress1)
    {
        $this->customerAddress1 = $customerAddress1;
    }
}
