<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class PropertyResidents
{
    /**
     * @Serializer\SerializedName("Residents")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Residents")
     */
    protected $residents = [];

    /**
     * @return Residents
     */
    public function getResidents()
    {
        return $this->residents;
    }

    /**
     * @param Residents $residents
     */
    public function setResidents($residents)
    {
        $this->residents = $residents;
    }
}
