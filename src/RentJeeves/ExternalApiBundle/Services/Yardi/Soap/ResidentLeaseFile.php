<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class ResidentLeaseFile 
{
    /**
     * @Serializer\SerializedName("Unit")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\LeaseFileUnit")
     */
    protected $unit;

    /**
     * @Serializer\SerializedName("MoveInDate")
     * @Serializer\Type("string")
     */
    protected $moveInDate;

    /**
     * @Serializer\SerializedName("LeaseBegin")
     * @Serializer\Type("string")
     */
    protected $leaseBegin;

    /**
     * @Serializer\SerializedName("LeaseBegin")
     * @Serializer\Type("string")
     */
    protected $leaseEnd;

    /**
     * @Serializer\SerializedName("MonthlyRentAmount")
     * @Serializer\Type("string")
     */
    protected $monthlyRentAmount;

    /**
     * @Serializer\SerializedName("DueDay")
     * @Serializer\Type("string")
     */
    protected $dueDay;

    /**
     * @Serializer\SerializedName("Tenants")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\LeaseFileTenant")
     */
    protected $tenantDetails;

    /**
     * @return mixed
     */
    public function getDueDay()
    {
        return $this->dueDay;
    }

    /**
     * @param mixed $dueDay
     */
    public function setDueDay($dueDay)
    {
        $this->dueDay = $dueDay;
    }

    /**
     * @return mixed
     */
    public function getLeaseBegin()
    {
        return $this->leaseBegin;
    }

    /**
     * @param mixed $leaseBegin
     */
    public function setLeaseBegin($leaseBegin)
    {
        $this->leaseBegin = $leaseBegin;
    }

    /**
     * @return mixed
     */
    public function getLeaseEnd()
    {
        return $this->leaseEnd;
    }

    /**
     * @param mixed $leaseEnd
     */
    public function setLeaseEnd($leaseEnd)
    {
        $this->leaseEnd = $leaseEnd;
    }

    /**
     * @return mixed
     */
    public function getMonthlyRentAmount()
    {
        return $this->monthlyRentAmount;
    }

    /**
     * @param mixed $monthlyRentAmount
     */
    public function setMonthlyRentAmount($monthlyRentAmount)
    {
        $this->monthlyRentAmount = $monthlyRentAmount;
    }

    /**
     * @return mixed
     */
    public function getMoveInDate()
    {
        return $this->moveInDate;
    }

    /**
     * @param mixed $moveInDate
     */
    public function setMoveInDate($moveInDate)
    {
        $this->moveInDate = $moveInDate;
    }

    /**
     * @return mixed
     */
    public function getTenantDetails()
    {
        return $this->tenantDetails;
    }

    /**
     * @param mixed $tenantDetails
     */
    public function setTenantDetails($tenantDetails)
    {
        $this->tenantDetails = $tenantDetails;
    }

    /**
     * @return mixed
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param mixed $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }
} 
