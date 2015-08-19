<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

class Information
{
    /**
     * @Serializer\SerializedName("MarketRent")
     * @Serializer\Type("float")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlElement(namespace="http://www.w3.org/2005/Atom")
     */
    protected $marketRent;

    /**
     * @Serializer\SerializedName("Address")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\Address")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlElement(namespace="http://www.w3.org/2005/Atom")
     */
    protected $address;

    /**
     * @Serializer\SerializedName("BuildingID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlElement(namespace="http://www.w3.org/2005/Atom")
     */
    protected $buildingID;

    /**
     * @return string
     */
    public function getBuildingID()
    {
        return $this->buildingID;
    }

    /**
     * @param string $buildingID
     */
    public function setBuildingID($buildingID)
    {
        $this->buildingID = $buildingID;
    }

    /**
     * @return float
     */
    public function getMarketRent()
    {
        return $this->marketRent;
    }

    /**
     * @param float $marketRent
     */
    public function setMarketRent($marketRent)
    {
        $this->marketRent = $marketRent;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param Address $address
     */
    public function setAddress(Address $address)
    {
        $this->address = $address;
    }
}
