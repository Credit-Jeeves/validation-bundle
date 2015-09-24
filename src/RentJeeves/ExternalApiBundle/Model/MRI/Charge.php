<?php

namespace RentJeeves\ExternalApiBundle\Model\MRI;

use JMS\Serializer\Annotation as Serializer;

class Charge
{
    /**
     * @Serializer\SerializedName("PropertyID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $propertyId;

    /**
     * @Serializer\SerializedName("BuildingID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $buildingId;

    /**
     * @Serializer\SerializedName("UnitID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $unitId;

    /**
     * @Serializer\SerializedName("LeaseID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $leaseId;

    /**
     * @Serializer\SerializedName("ChargeCode")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $chargeCode;

    /**
     * @Serializer\SerializedName("Description")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $description;

    /**
     * @Serializer\SerializedName("Amount")
     * @Serializer\Type("double")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $amount;

    /**
     * @Serializer\SerializedName("Frequency")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $frequency;

    /**
     * @Serializer\SerializedName("EffectiveDate")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $effectiveDate;

    /**
     * @Serializer\SerializedName("EndDate")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $endDate;

    /**
     * @Serializer\SerializedName("IsConcession")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $isConcession;

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * @param string $buildingId
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;
    }

    /**
     * @return string
     */
    public function getChargeCode()
    {
        return $this->chargeCode;
    }

    /**
     * @param string $chargeCode
     */
    public function setChargeCode($chargeCode)
    {
        $this->chargeCode = $chargeCode;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getEffectiveDate()
    {
        return $this->effectiveDate;
    }

    /**
     * @param string $effectiveDate
     */
    public function setEffectiveDate($effectiveDate)
    {
        $this->effectiveDate = $effectiveDate;
    }

    /**
     * @return string
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param string $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return string
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @param string $frequency
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
    }

    /**
     * @return string
     */
    public function getIsConcession()
    {
        return $this->isConcession;
    }

    /**
     * @param string $isConcession
     */
    public function setIsConcession($isConcession)
    {
        $this->isConcession = $isConcession;
    }

    /**
     * @return string
     */
    public function getLeaseId()
    {
        return $this->leaseId;
    }

    /**
     * @param string $leaseId
     */
    public function setLeaseId($leaseId)
    {
        $this->leaseId = $leaseId;
    }

    /**
     * @return string
     */
    public function getPropertyId()
    {
        return $this->propertyId;
    }

    /**
     * @param string $propertyId
     */
    public function setPropertyId($propertyId)
    {
        $this->propertyId = $propertyId;
    }

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
     * @return \DateTime
     */
    public function getDateTimeEffectiveDate()
    {
        if (empty($this->effectiveDate)) {
            return null;
        }

        return \DateTime::createFromFormat(Value::DATE_FORMAT, $this->effectiveDate);
    }

    /**
     * @return \DateTime
     */
    public function getDateTimeEndDate()
    {
        if (empty($this->endDate)) {
            return null;
        }

        return \DateTime::createFromFormat(Value::DATE_FORMAT, $this->endDate);
    }
}
