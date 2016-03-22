<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

class RtUnit
{
    /**
     * @Serializer\SerializedName("UnitID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $unitId;

    /**
     * @Serializer\SerializedName("Unit")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\Unit")
     * @Serializer\Groups({"ResMan"})
     */
    protected $unit;

    /**
     * @return Unit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param Unit $unit
     */
    public function setUnit(Unit $unit)
    {
        $this->unit = $unit;
    }

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
    public function getExternalUnitId()
    {
        return sprintf(
            '%s|%s|%s',
            $this->getUnit()->getPropertyPrimaryID(),
            $this->getUnit()->getInformation()->getBuildingID(),
            $this->getUnitId()
        );
    }
}
