<?php

namespace RentJeeves\ExternalApiBundle\Model\Yardi;

use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Property as YardiProperty;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\UnitInformationFullUnit as YardiUnit;

class UnitInformation
{
    /**
     * @var YardiProperty
     */
    protected $property;

    /**
     * @var YardiUnit
     */
    protected $unit;

    /**
     * @return YardiProperty
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param YardiProperty $property
     */
    public function setProperty(YardiProperty $property)
    {
        $this->property = $property;
    }

    /**
     * @return YardiUnit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param YardiUnit $unit
     */
    public function setUnit(YardiUnit $unit)
    {
        $this->unit = $unit;
    }
}
