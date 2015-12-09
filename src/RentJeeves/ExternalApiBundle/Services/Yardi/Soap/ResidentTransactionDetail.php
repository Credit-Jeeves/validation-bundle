<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class ResidentTransactionDetail
{
    /**
     * @Serializer\SerializedName("BalanceDue")
     * @Serializer\Type("double")
     */
    protected $balanceDue;

    /**
     * @Serializer\SerializedName("ServiceToDate")
     * @Serializer\Type("string")
     */
    protected $serviceToDate;

    /**
     * @Serializer\SerializedName("ServiceFromDate")
     * @Serializer\Type("string")
     */
    protected $serviceFromDate;

    /**
     * @Serializer\SerializedName("Amount")
     * @Serializer\Type("double")
     */
    protected $amount;

    /**
     * @Serializer\SerializedName("ChargeCode")
     * @Serializer\Type("string")
     */
    protected $chargeCode;

    /**
     * @Serializer\SerializedName("CustomerID")
     * @Serializer\Type("string")
     */
    protected $customerID;

    /**
     * @Serializer\SerializedName("UnitID")
     * @Serializer\Type("string")
     */
    protected $unitID;

    /**
     * @param float $balanceDue
     */
    public function setBalanceDue($balanceDue)
    {
        $this->balanceDue = $balanceDue;
    }

    /**
     * @return float
     */
    public function getBalanceDue()
    {
        return $this->balanceDue;
    }

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
    public function getCustomerID()
    {
        return $this->customerID;
    }

    /**
     * @param string $customerID
     */
    public function setCustomerID($customerID)
    {
        $this->customerID = $customerID;
    }

    /**
     * @return string
     */
    public function getUnitID()
    {
        return $this->unitID;
    }

    /**
     * @param string $unitID
     */
    public function setUnitID($unitID)
    {
        $this->unitID = $unitID;
    }

    /**
     * @return string
     */
    public function getServiceToDate()
    {
        return $this->serviceToDate;
    }

    /**
     * @param string $serviceToDate
     */
    public function setServiceToDate($serviceToDate)
    {
        $this->serviceToDate = $serviceToDate;
    }

    /**
     * @return bool|\DateTime
     */
    public function getServiceToDateObject()
    {
        if ($this->serviceToDate) {
            return \DateTime::createFromFormat('Y-m-d', $this->serviceToDate);
        }

        return null;
    }

    /**
     * @return string
     */
    public function getServiceFromDate()
    {
        return $this->serviceFromDate;
    }

    /**
     * @param string $serviceFromDate
     */
    public function setServiceFromDate($serviceFromDate)
    {
        $this->serviceFromDate = $serviceFromDate;
    }

    /**
     * @return bool|\DateTime
     */
    public function getServiceFromDateObject()
    {
        if ($this->serviceFromDate) {
            return \DateTime::createFromFormat('Y-m-d', $this->serviceFromDate);
        }

        return null;
    }

    /**
     * @param string $externalPropertyId
     * @return string
     */
    public function getExternalUnitId($externalPropertyId)
    {
        return sprintf('%s||%s', $externalPropertyId, $this->getUnitID());
    }
}
