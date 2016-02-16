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
     * @param mixed $property
     */
    public function setProperty($property)
    {
        $this->property[] = $property;
    }

    /**
     * @return \RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Property[]
     */
    public function getProperty()
    {
        return $this->property;
    }
}
