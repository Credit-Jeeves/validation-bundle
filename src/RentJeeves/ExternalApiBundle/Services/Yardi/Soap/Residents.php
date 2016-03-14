<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class Residents 
{
    /**
     * @Serializer\SerializedName("Resident")
     * @Serializer\XmlList(inline = true, entry="Resident")
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentsResident>")
     */
    protected $residents = [];

    /**
     * @return ResidentsResident[]
     */
    public function getResidents()
    {
        return $this->residents;
    }

    /**
     * @param ResidentsResident $residents
     */
    public function setResidents($residents)
    {
        $this->residents = $residents;
    }
} 
