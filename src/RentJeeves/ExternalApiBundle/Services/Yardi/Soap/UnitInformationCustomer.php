<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class UnitInformationCustomer
{
    /**
     * @var string
     *
     * @Serializer\SerializedName("CustomerID")
     * @Serializer\Type("string")
     */
    protected $customerId;

    /**
     * @var UnitInformationFullUnit
     *
     * @Serializer\SerializedName("RT_Unit")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\UnitInformationFullUnit")
     */
    protected $unit;

    /**
     * @param string $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param UnitInformationFullUnit $unit
     */
    public function setUnit(UnitInformationFullUnit $unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return UnitInformationFullUnit
     */
    public function getUnit()
    {
        return $this->unit;
    }
}
