<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class UnitInformationFullUnit
{
    /**
     * @var string
     *
     * @Serializer\SerializedName("UnitID")
     * @Serializer\Type("string")
     */
    protected $unitId;

    /**
     * @var string
     *
     * @Serializer\SerializedName("UnitIDValue")
     * @Serializer\Type("string")
     */
    protected $unitIdValue;

    /**
     * @var UnitInformationUnit
     *
     * @Serializer\SerializedName("Unit")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\UnitInformationUnit")
     */
    protected $unit;

    /**
     * @return string
     */
    public function getUnitId()
    {
        return $this->unitId;
    }

    /**
     * @param string $unitId
     */
    public function setUnitId($unitId)
    {
        $this->unitId = $unitId;
    }

    /**
     * @return string
     */
    public function getUnitIdValue()
    {
        return $this->unitIdValue;
    }

    /**
     * @param string $unitIdValue
     */
    public function setUnitIdValue($unitIdValue)
    {
        $this->unitIdValue = $unitIdValue;
    }

    /**
     * @return UnitInformationUnit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param UnitInformationUnit $unit
     */
    public function setUnit(UnitInformationUnit $unit)
    {
        $this->unit = $unit;
    }
}
