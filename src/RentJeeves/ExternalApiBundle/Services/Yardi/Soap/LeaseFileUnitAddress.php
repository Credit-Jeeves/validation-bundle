<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class LeaseFileUnitAddress
{
    /**
     * @Serializer\SerializedName("AddressLine1")
     * @Serializer\Type("string")
     */
    protected $unitAddressLine1;

    /**
     * @return mixed
     */
    public function getUnitAddressLine1()
    {
        return $this->unitAddressLine1;
    }

    /**
     * @param mixed $unitAddressLine1
     */
    public function setUnitAddressLine1($unitAddressLine1)
    {
        $this->unitAddressLine1 = $unitAddressLine1;
    }
}
