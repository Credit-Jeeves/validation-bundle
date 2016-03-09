<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Properties")
 */
class GetPropertyConfigurationsResponse
{
    /**
     * @Serializer\SerializedName("Property")
     * @Serializer\XmlList(inline = true, entry="Property")
     * @Serializer\XmlKeyValuePairs
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Property>")
     */
    protected $property = array();

    /**
     * @param Property $property
     */
    public function setProperty($property)
    {
        $this->property[] = $property;
    }

    /**
     * @return Property[]
     */
    public function getProperty()
    {
        return $this->property;
    }
}
