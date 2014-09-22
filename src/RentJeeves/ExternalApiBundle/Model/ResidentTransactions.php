<?php

namespace RentJeeves\ExternalApiBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\YardiSettings;

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

    public function __construct(YardiSettings $yardiSettings, $orders = null)
    {
        $this->property = new Property($yardiSettings, $orders);
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
