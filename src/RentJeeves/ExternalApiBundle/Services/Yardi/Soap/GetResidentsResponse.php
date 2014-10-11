<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("MITS-ResidentData")
 */
class GetResidentsResponse 
{
    /**
     * @Serializer\SerializedName("PropertyResidents")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\PropertyResidents")
     */
    protected $propertyResidents;

    /**
     * @return mixed
     */
    public function getPropertyResidents()
    {
        return $this->propertyResidents;
    }

    /**
     * @param mixed $propertyResidents
     */
    public function setPropertyResidents($propertyResidents)
    {
        $this->propertyResidents = $propertyResidents;
    }
} 
