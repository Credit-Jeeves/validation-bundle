<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("PhysicalProperty")
 */
class GetUnitInformationResponse
{
    /**
     * @var UnitInformationProperty
     *
     * @Serializer\SerializedName("Property")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\UnitInformationProperty")
     */
    protected $property;

    /**
     * @param UnitInformationProperty $property
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }

    /**
     * @return UnitInformationProperty
     */
    public function getProperty()
    {
        return $this->property;
    }
}
