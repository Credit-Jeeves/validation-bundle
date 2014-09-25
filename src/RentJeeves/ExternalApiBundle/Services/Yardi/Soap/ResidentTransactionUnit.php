<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class ResidentTransactionUnit
{
    /**
     * @Serializer\SerializedName("UnitID")
     * @Serializer\Type("string")
     */
    protected $unitId;

    /**
     * @param mixed $unitId
     */
    public function setUnitId($unitId)
    {
        $this->unitId = $unitId;
    }

    /**
     * @return mixed
     */
    public function getUnitId()
    {
        return $this->unitId;
    }
}
