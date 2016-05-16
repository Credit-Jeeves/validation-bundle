<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class UnitInformation
{
    /**
     * @var string
     *
     * @Serializer\SerializedName("UnitID")
     * @Serializer\Type("string")
     */
    protected $unitId;

    /**
     * @var string
     *
     * @Serializer\SerializedName("UnitType")
     * @Serializer\Type("string")
     */
    protected $unitType;

    /**
     * @var integer
     *
     * @Serializer\SerializedName("UnitBedrooms")
     * @Serializer\Type("integer")
     */
    protected $unitBedrooms;

    /**
     * @var integer
     *
     * @Serializer\SerializedName("UnitBathrooms")
     * @Serializer\Type("integer")
     */
    protected $unitBathrooms;

    /**
     * @var float
     *
     * @Serializer\SerializedName("MinSquareFeet")
     * @Serializer\Type("float")
     */
    protected $minSquareFeet;

    /**
     * @var float
     *
     * @Serializer\SerializedName("MaxSquareFeet")
     * @Serializer\Type("float")
     */
    protected $maxSquareFeet;

    /**
     * @var float
     *
     * @Serializer\SerializedName("MarketRent")
     * @Serializer\Type("float")
     */
    protected $marketRent;

    /**
     * @var string
     *
     * @Serializer\SerializedName("UnitEconomicStatus")
     * @Serializer\Type("string")
     */
    protected $uitEconomicStatus;

    /**
     * @var string
     *
     * @Serializer\SerializedName("UnitEconomicStatusDescription")
     * @Serializer\Type("string")
     */
    protected $unitEconomicStatusDescription;

    /**
     * @var string
     *
     * @Serializer\SerializedName("FloorPlanID")
     * @Serializer\Type("string")
     */
    protected $floorPlanId;

    /**
     * @var string
     *
     * @Serializer\SerializedName("FloorplanName")
     * @Serializer\Type("string")
     */
    protected $floorplanName;

    /**
     * @var string|null
     *
     * @Serializer\SerializedName("BuildingID")
     * @Serializer\Type("string")
     */
    protected $buildingId;

    /**
     * @var UnitInformationAddress
     *
     * @Serializer\SerializedName("Address")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\UnitInformationAddress")
     */
    protected $address;

    /**
     * @return string
     */
    public function getUnitId()
    {
        return $this->unitId;
    }

    /**
     * @param string $unitId
     */
    public function setUnitId($unitId)
    {
        $this->unitId = $unitId;
    }

    /**
     * @return string
     */
    public function getUnitType()
    {
        return $this->unitType;
    }

    /**
     * @param string $unitType
     */
    public function setUnitType($unitType)
    {
        $this->unitType = $unitType;
    }

    /**
     * @return int
     */
    public function getUnitBedrooms()
    {
        return $this->unitBedrooms;
    }

    /**
     * @param int $unitBedrooms
     */
    public function setUnitBedrooms($unitBedrooms)
    {
        $this->unitBedrooms = $unitBedrooms;
    }

    /**
     * @return int
     */
    public function getUnitBathrooms()
    {
        return $this->unitBathrooms;
    }

    /**
     * @param int $unitBathrooms
     */
    public function setUnitBathrooms($unitBathrooms)
    {
        $this->unitBathrooms = $unitBathrooms;
    }

    /**
     * @return float
     */
    public function getMinSquareFeet()
    {
        return $this->minSquareFeet;
    }

    /**
     * @param float $minSquareFeet
     */
    public function setMinSquareFeet($minSquareFeet)
    {
        $this->minSquareFeet = $minSquareFeet;
    }

    /**
     * @return float
     */
    public function getMaxSquareFeet()
    {
        return $this->maxSquareFeet;
    }

    /**
     * @param float $maxSquareFeet
     */
    public function setMaxSquareFeet($maxSquareFeet)
    {
        $this->maxSquareFeet = $maxSquareFeet;
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
     * @return string
     */
    public function getUitEconomicStatus()
    {
        return $this->uitEconomicStatus;
    }

    /**
     * @param string $uitEconomicStatus
     */
    public function setUitEconomicStatus($uitEconomicStatus)
    {
        $this->uitEconomicStatus = $uitEconomicStatus;
    }

    /**
     * @return string
     */
    public function getUnitEconomicStatusDescription()
    {
        return $this->unitEconomicStatusDescription;
    }

    /**
     * @param string $unitEconomicStatusDescription
     */
    public function setUnitEconomicStatusDescription($unitEconomicStatusDescription)
    {
        $this->unitEconomicStatusDescription = $unitEconomicStatusDescription;
    }

    /**
     * @return string
     */
    public function getFloorPlanId()
    {
        return $this->floorPlanId;
    }

    /**
     * @param string $floorPlanId
     */
    public function setFloorPlanId($floorPlanId)
    {
        $this->floorPlanId = $floorPlanId;
    }

    /**
     * @return string
     */
    public function getFloorplanName()
    {
        return $this->floorplanName;
    }

    /**
     * @param string $floorplanName
     */
    public function setFloorplanName($floorplanName)
    {
        $this->floorplanName = $floorplanName;
    }

    /**
     * @return null|string
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * @param null|string $buildingId
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;
    }

    /**
     * @return UnitInformationAddress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param UnitInformationAddress $address
     */
    public function setAddress(UnitInformationAddress $address)
    {
        $this->address = $address;
    }
}
