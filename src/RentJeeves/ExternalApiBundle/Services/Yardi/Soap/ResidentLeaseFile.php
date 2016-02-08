<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;
use DateTime;

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
     * @Serializer\SerializedName("LeaseEnd")
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
     * @Serializer\SerializedName("Ledger")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\LeaseFileLedger")
     */
    protected $ledgerDetails;

    /**
     * @return LeaseFileLedger|null
     */
    public function getLedgerDetails()
    {
        return $this->ledgerDetails;
    }

    /**
     * @param mixed $ledgerDetails
     */
    public function setLedgerDetails($ledgerDetails)
    {
        $this->ledgerDetails = $ledgerDetails;
    }

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
     * @param bool $returnObject
     * @return DateTime
     */
    public function getLeaseEnd($returnObject = false)
    {
        if (!empty($this->leaseEnd) && is_string($this->leaseEnd) && $returnObject) {
            return DateTime::createFromFormat('Y-m-d', $this->leaseEnd);
        }

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
     * @return LeaseFileTenant
     */
    public function getTenantDetails()
    {
        return $this->tenantDetails;
    }

    /**
     * @param LeaseFileTenant $tenantDetails
     */
    public function setTenantDetails(LeaseFileTenant $tenantDetails)
    {
        $this->tenantDetails = $tenantDetails;
    }

    /**
     * @return LeaseFileUnit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param LeaseFileUnit $unit
     */
    public function setUnit(LeaseFileUnit $unit)
    {
        $this->unit = $unit;
    }
}
