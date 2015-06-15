<?php

namespace RentJeeves\ExternalApiBundle\Model\MRI;

use JMS\Serializer\Annotation as Serializer;

class Value
{
    const DATE_FORMAT = 'Y-m-d?H:i:s.u*';

    /**
     * @Serializer\SerializedName("ResidentNameID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $residentId;

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
     * @Serializer\SerializedName("Address")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $address;

    /**
     * @Serializer\SerializedName("City")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $city;

    /**
     * @Serializer\SerializedName("State")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $state;

    /**
     * @Serializer\SerializedName("Zipcode")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $zipCode;

    /**
     * @Serializer\SerializedName("FirstName")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $firstName;

    /**
     * @Serializer\SerializedName("LastName")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $lastName;

    /**
     * @Serializer\SerializedName("Email")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $email;

    /**
     * @Serializer\SerializedName("Birthday")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $birthday;

    /**
     * @Serializer\SerializedName("LeaseStart")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $leaseStart;

    /**
     * @Serializer\SerializedName("LeaseEnd")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $leaseEnd;

    /**
     * @Serializer\SerializedName("LeaseMonthlyRentAmount")
     * @Serializer\Type("double")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $leaseMonthlyRentAmount;

    /**
     * @Serializer\SerializedName("LeaseMoveOut")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $leaseMoveOut;

    /**
     * @Serializer\SerializedName("LeaseMonthToMonth")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $leaseMonthToMonth;

    /**
     * @Serializer\SerializedName("PayAllowed")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $payAllowed;

    /**
     * @Serializer\SerializedName("LastUpdateDate")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $lastUpdateDate;

    /**
     * @Serializer\SerializedName("CurrCode")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $currCode;

    /**
     * @Serializer\SerializedName("LeaseBalance")
     * @Serializer\Type("double")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $leaseBalance;

    /**
     * @Serializer\SerializedName("IsCurrent")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $isCurrent;

    /**
     * @Serializer\SerializedName("OccupyDate")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $occupyDate;

    /**
     * @Serializer\SerializedName("BuildingAddress")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $buildingAddress;

    /**
     * @return string
     */
    public function getBuildingAddress()
    {
        return $this->buildingAddress;
    }

    /**
     * @param string $buildingAddress
     */
    public function setBuildingAddress($buildingAddress)
    {
        $this->buildingAddress = $buildingAddress;
    }

    /**
     * @return string
     */
    public function getIsCurrent()
    {
        return $this->isCurrent;
    }

    /**
     * @param string $isCurrent
     */
    public function setIsCurrent($isCurrent)
    {
        $this->isCurrent = $isCurrent;
    }

    /**
     * @return string
     */
    public function getOccupyDate()
    {
        return $this->occupyDate;
    }

    /**
     * @return \DateTime
     */
    public function getOccupyDateFormatted()
    {
        if (!empty($this->occupyDate)) {
            return \DateTime::createFromFormat(self::DATE_FORMAT, $this->occupyDate);
        }

        return $this->occupyDate;
    }

    /**
     * @param string $occupyDate
     */
    public function setOccupyDate($occupyDate)
    {
        $this->occupyDate = $occupyDate;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param string $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
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
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCurrCode()
    {
        return $this->currCode;
    }

    /**
     * @param string $currCode
     */
    public function setCurrCode($currCode)
    {
        $this->currCode = $currCode;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getLastUpdateDate()
    {
        if (!empty($this->lastUpdateDate)) {
            return \DateTime::createFromFormat(self::DATE_FORMAT, $this->lastUpdateDate);
        }

        return $this->lastUpdateDate;
    }

    /**
     * @param string $lastUpdateDate
     */
    public function setLastUpdateDate($lastUpdateDate)
    {
        $this->lastUpdateDate = $lastUpdateDate;
    }

    /**
     * @return string
     */
    public function getLeaseBalance()
    {
        return $this->leaseBalance;
    }

    /**
     * @param string $leaseBalance
     */
    public function setLeaseBalance($leaseBalance)
    {
        $this->leaseBalance = $leaseBalance;
    }

    /**
     * @return null|\DateTime
     */
    public function getLeaseEnd()
    {
        if (!empty($this->leaseEnd)) {
            return \DateTime::createFromFormat(self::DATE_FORMAT, $this->leaseEnd);
        }

        return $this->leaseEnd;
    }

    /**
     * @param string $leaseEnd
     */
    public function setLeaseEnd($leaseEnd)
    {
        $this->leaseEnd = $leaseEnd;
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
    public function getLeaseMonthToMonth()
    {
        return $this->leaseMonthToMonth;
    }

    /**
     * @param string $leaseMonthToMonth
     */
    public function setLeaseMonthToMonth($leaseMonthToMonth)
    {
        $this->leaseMonthToMonth = $leaseMonthToMonth;
    }

    /**
     * @return string
     */
    public function getLeaseMonthlyRentAmount()
    {
        return $this->leaseMonthlyRentAmount;
    }

    /**
     * @param string $leaseMonthlyRentAmount
     */
    public function setLeaseMonthlyRentAmount($leaseMonthlyRentAmount)
    {
        $this->leaseMonthlyRentAmount = $leaseMonthlyRentAmount;
    }

    /**
     * @return null|\DateTime
     */
    public function getLeaseMoveOut()
    {
        if (!empty($this->leaseMoveOut)) {
            return \DateTime::createFromFormat(self::DATE_FORMAT, $this->leaseMoveOut);
        }

        return $this->leaseMoveOut;
    }

    /**
     * @param string $leaseMoveOut
     */
    public function setLeaseMoveOut($leaseMoveOut)
    {
        $this->leaseMoveOut = $leaseMoveOut;
    }

    /**
     * @return null|\DateTime
     */
    public function getLeaseStart()
    {
        if (!empty($this->leaseStart)) {
            return \DateTime::createFromFormat(self::DATE_FORMAT, $this->leaseStart);
        }

        return $this->leaseStart;
    }

    /**
     * @param string $leaseStart
     */
    public function setLeaseStart($leaseStart)
    {
        $this->leaseStart = $leaseStart;
    }

    /**
     * @return string
     */
    public function getPayAllowed()
    {
        return $this->payAllowed;
    }

    /**
     * @param string $payAllowed
     */
    public function setPayAllowed($payAllowed)
    {
        $this->payAllowed = $payAllowed;
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
    public function getResidentId()
    {
        return $this->residentId;
    }

    /**
     * @param string $residentId
     */
    public function setResidentId($residentId)
    {
        $this->residentId = $residentId;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
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
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * @param string $zipCode
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
    }
}
