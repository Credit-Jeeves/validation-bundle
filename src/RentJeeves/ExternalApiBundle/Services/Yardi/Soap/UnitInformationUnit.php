<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class UnitInformationUnit
{
    /**
     * @var UnitInformation
     *
     * @Serializer\SerializedName("Information")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\UnitInformation")
     */
    protected $information;

    /**
     * @var string
     *
     * @Serializer\SerializedName("PropertyPrimaryID")
     * @Serializer\Type("string")
     */
    protected $propertyPrimaryId;

    /**
     * @var string
     *
     * @Serializer\SerializedName("MarketingName")
     * @Serializer\Type("string")
     */
    protected $marketingName;

    /**
     * @return UnitInformation
     */
    public function getInformation()
    {
        return $this->information;
    }

    /**
     * @param UnitInformation $information
     */
    public function setInformation(UnitInformation $information)
    {
        $this->information = $information;
    }

    /**
     * @return string
     */
    public function getPropertyPrimaryId()
    {
        return $this->propertyPrimaryId;
    }

    /**
     * @param string $propertyPrimaryId
     */
    public function setPropertyPrimaryId($propertyPrimaryId)
    {
        $this->propertyPrimaryId = $propertyPrimaryId;
    }

    /**
     * @return string
     */
    public function getMarketingName()
    {
        return $this->marketingName;
    }

    /**
     * @param string $marketingName
     */
    public function setMarketingName($marketingName)
    {
        $this->marketingName = $marketingName;
    }
}
