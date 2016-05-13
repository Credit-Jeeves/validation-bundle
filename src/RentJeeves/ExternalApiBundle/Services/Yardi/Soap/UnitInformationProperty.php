<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class UnitInformationProperty
{
    /**
     * @var UnitInformationCustomer[]
     *
     * @Serializer\SerializedName("RT_Customer")
     * @Serializer\XmlList(inline = true, entry="RT_Customer")
     * @Serializer\XmlKeyValuePairs
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Services\Yardi\Soap\UnitInformationCustomer>")
     */
    protected $customers= [];

    /**
     * @param UnitInformationCustomer $customer
     */
    public function setCustomer(UnitInformationCustomer $customer)
    {
        $this->customers[] = $customer;
    }

    /**
     * @return UnitInformationCustomer[]
     */
    public function getCustomers()
    {
        return $this->customers;
    }
}
