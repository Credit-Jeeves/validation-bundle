<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("ResidentTransactions")
 */
class ResidentTransactions
{
    /**
     * @Serializer\SerializedName("Property")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\Property")
     * @Serializer\Groups({"ResMan"})
     */
    protected $property;

    /**
     * @return Property
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param Property $property
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }
}
