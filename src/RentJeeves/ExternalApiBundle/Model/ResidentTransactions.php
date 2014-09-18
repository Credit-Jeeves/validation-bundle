<?php

namespace RentJeeves\ExternalApiBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("ResidentTransactions")
 */
class ResidentTransactions
{

    /**
     * @Serializer\SerializedName("Property")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\Property")
     * @Serializer\Groups({"soapYardiRequest"})
     */
    protected $property;

    public function __construct($orders = null)
    {
        $this->property = new Property($orders);
    }

    /**
     * @param Property $property
     */
    public function setProperty(Property $property)
    {
        $this->property = $property;
    }

    /**
     * @return Property
     */
    public function getProperty()
    {
        return $this->property;
    }
}
