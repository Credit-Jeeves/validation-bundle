<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Property")
 */
class PropertyCustomer
{
    /**
     * @Serializer\SerializedName("PrimaryID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlElement(namespace="http://www.w3.org/2005/Atom")
     */
    protected $primaryId;

    /**
     * @return string
     */
    public function getPrimaryId()
    {
        return $this->primaryId;
    }

    /**
     * @param string $primaryId
     */
    public function setPrimaryId($primaryId)
    {
        $this->primaryId = $primaryId;
    }
}
