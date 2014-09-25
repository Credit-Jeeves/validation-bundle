<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class ResidentTransactionProperty
{
    /**
     * @Serializer\SerializedName("RT_Customer")
     * @Serializer\XmlList(inline = true, entry="RT_Customer")
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionPropertyCustomer>")
     */
    protected $customers = [];

    /**
     * @param mixed $customer
     */
    public function setCustomers($customers)
    {
        $this->customers[] = $customers;
    }

    /**
     * @return mixed
     */
    public function getCustomers()
    {
        return $this->customers;
    }
}
