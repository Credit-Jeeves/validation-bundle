<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

class Unit
{
    /**
     * @Serializer\SerializedName("Information")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\Information")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlElement(namespace="http://www.w3.org/2005/Atom")
     */
    protected $information;

    /**
     * @Serializer\SerializedName("PropertyPrimaryID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlElement(namespace="http://www.w3.org/2005/Atom")
     */
    protected $propertyPrimaryID;

    /**
     * @return string
     */
    public function getPropertyPrimaryID()
    {
        return $this->propertyPrimaryID;
    }

    /**
     * @param string $propertyPrimaryID
     */
    public function setPropertyPrimaryID($propertyPrimaryID)
    {
        $this->propertyPrimaryID = $propertyPrimaryID;
    }

    /**
     * @return Information
     */
    public function getInformation()
    {
        return $this->information;
    }

    /**
     * @param Information $information
     */
    public function setInformation(Information $information)
    {
        $this->information = $information;
    }
}
