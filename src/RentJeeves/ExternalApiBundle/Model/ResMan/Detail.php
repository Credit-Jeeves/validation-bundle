<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

class Detail
{
    /**
     * @Serializer\SerializedName("TransactionDate")
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @Serializer\Groups({"ResMan"})
     */
    protected $transactionDate;
    /**
     * @Serializer\SerializedName("Description")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $description;
    /**
     * @Serializer\SerializedName("ChargeCode")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $chargeCode;
    /**
     * @Serializer\SerializedName("GLAccountNumber")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $GLAccountNumber;
    /**
     * @Serializer\SerializedName("CustomerID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $customerId;

    /**
     * @Serializer\SerializedName("UnitID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $unitID;

    /**
     * @Serializer\SerializedName("Amount")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $amount;

    /**
     * @return string
     */
    public function getGLAccountNumber()
    {
        return $this->GLAccountNumber;
    }

    /**
     * @param string $GLAccountNumber
     */
    public function setGLAccountNumber($GLAccountNumber)
    {
        $this->GLAccountNumber = $GLAccountNumber;
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
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
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
     * @return \DateTime
     */
    public function getTransactionDate()
    {
        return $this->transactionDate;
    }

    /**
     * @param \DateTime $transactionDate
     */
    public function setTransactionDate(\DateTime$transactionDate)
    {
        $this->transactionDate = $transactionDate;
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
}
