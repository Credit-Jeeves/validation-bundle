<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class LeaseFileUnit
{
    /**
     * @Serializer\SerializedName("Identification")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\LeaseFileUnitIdentification")
     */
    protected $identification;

    /**
     * @Serializer\SerializedName("UnitRent")
     * @Serializer\Type("string")
     */
    protected $unitRent;

    /**
     * @Serializer\SerializedName("Address")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\LeaseFileUnitAddress")
     */
    protected $unitAddress;

    /**
     * @return mixed
     */
    public function getIdentification()
    {
        return $this->identification;
    }

    /**
     * @param mixed $identification
     */
    public function setIdentification($identification)
    {
        $this->identification = $identification;
    }

    /**
     * @return mixed
     */
    public function getUnitRent()
    {
        return $this->unitRent;
    }

    /**
     * @param mixed $unitRent
     */
    public function setUnitRent($unitRent)
    {
        $this->unitRent = $unitRent;
    }

    /**
     * @return mixed
     */
    public function getUnitAddress()
    {
        return $this->unitRent;
    }

    /**
     * @param mixed $unitAddress
     */
    public function setUnitAddress($unitAddress)
    {
        $this->unitAddress = $unitAddress;
    }
}
