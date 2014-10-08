<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class LeaseFileLedger
{
    /**
     * @Serializer\SerializedName("Identification")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\LeaseFileIdentification")
     */
    protected $identification;

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

} 