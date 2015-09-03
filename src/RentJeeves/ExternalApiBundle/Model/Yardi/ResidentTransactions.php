<?php

namespace RentJeeves\ExternalApiBundle\Model\Yardi;

use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\YardiSettings;

/**
 * @Serializer\XmlRoot("ResidentTransactions")
 */
class ResidentTransactions
{

    /**
     * @Serializer\SerializedName("Property")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\Yardi\Property")
     * @Serializer\Groups({"baseRequest", "withPostMonth", "reversedPayment"})
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
