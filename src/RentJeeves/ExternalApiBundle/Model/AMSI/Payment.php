<?php

namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

class Payment
{
    /**
     * @Serializer\SerializedName("PropertyID")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $propertyId;

    /**
     * @Serializer\SerializedName("BldgID")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $bldgId;

    /**
     * @Serializer\SerializedName("UnitID")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $unitId;

    /**
     * @Serializer\SerializedName("ResiID")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $resiId;

    /**
     * @Serializer\SerializedName("ClientMerchantID")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     * @Serializer\Type("integer")
     *
     * @var int
     */
    protected $clientMerchantId;

    /**
     * @Serializer\SerializedName("ClientTransactionID")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     * @Serializer\Type("integer")
     *
     * @var int
     */
    protected $clientTransactionId;

    /**
     * @Serializer\SerializedName("ClientTransactionDate")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     * @Serializer\Type("DateTime<'m/d/Y'>")
     *
     * @var \DateTime
     */
    protected $clientTransactionDate;

    /**
     * @Serializer\SerializedName("Amount")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     * @Serializer\Type("double")
     *
     * @var double
     */
    protected $amount;

    /**
     * @Serializer\SerializedName("ClientJnlNo")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     * @Serializer\Type("integer")
     *
     * @var int
     */
    protected $clientJnlNo;

    /**
     * @Serializer\SerializedName("Description")
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     *
     * @var string
     */
    protected $description = '';

    /**
     * @Serializer\SerializedName("PaymentType")
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     *
     * @var string
     */
    protected $paymentType = '';

    /**
     * @Serializer\SerializedName("CheckNo")
     * @Serializer\Type("integer")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     *
     * @var string
     */
    protected $checkNo = '';

    /**
     * @Serializer\SerializedName("ChargeIncCode")
     * @Serializer\Type("integer")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     *
     * @var string
     */
    protected $chargeIncCode = '';

    /**
     * @Serializer\SerializedName("ChargeDateFrom")
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     *
     * @var string
     */
    protected $chargeDateFrom = '';

    /**
     * @Serializer\SerializedName("ChargeDateThru")
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     *
     * @var string
     */
    protected $chargeDateThru = '';

    /**
     * @Serializer\SerializedName("ErrorCode")
     * @Serializer\Type("integer")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"addPaymentResponse"})
     *
     * @var int
     */
    protected $errorCode;

    /**
     * @Serializer\SerializedName("ErrorDescription")
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"addPaymentResponse"})
     *
     * @var string
     */
    protected $errorDescription;

    /**
     * @Serializer\SerializedName("EvolutionReference")
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"addPaymentResponse"})
     *
     * @var string
     */
    protected $evolutionReference;

    /**
     * @Serializer\SerializedName("EvolutionReferenceDescription")
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"addPaymentResponse"})
     *
     * @var string
     */
    protected $evolutionReferenceDescription;

    /**
     * @Serializer\SerializedName("AdditionalData")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\AMSI\AdditionalData")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"addPaymentResponse"})
     *
     * @var AdditionalData
     */
    protected $additionalData;

    /**
     * @return double
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param double $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getBldgId()
    {
        return $this->bldgId;
    }

    /**
     * @param string $bldgId
     */
    public function setBldgId($bldgId)
    {
        $this->bldgId = $bldgId;
    }

    /**
     * @return int
     */
    public function getClientJnlNo()
    {
        return $this->clientJnlNo;
    }

    /**
     * @param int $clientJnlNo
     */
    public function setClientJnlNo($clientJnlNo)
    {
        $this->clientJnlNo = $clientJnlNo;
    }

    /**
     * @return int
     */
    public function getClientMerchantId()
    {
        return $this->clientMerchantId;
    }

    /**
     * @param int $clientMerchantId
     */
    public function setClientMerchantId($clientMerchantId)
    {
        $this->clientMerchantId = $clientMerchantId;
    }

    /**
     * @return \DateTime
     */
    public function getClientTransactionDate()
    {
        return $this->clientTransactionDate;
    }

    /**
     * @param \DateTime $clientTransactionDate
     */
    public function setClientTransactionDate($clientTransactionDate)
    {
        $this->clientTransactionDate = $clientTransactionDate;
    }

    /**
     * @return int
     */
    public function getClientTransactionId()
    {
        return $this->clientTransactionId;
    }

    /**
     * @param int $clientTransactionId
     */
    public function setClientTransactionId($clientTransactionId)
    {
        $this->clientTransactionId = $clientTransactionId;
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
    public function getResiId()
    {
        return $this->resiId;
    }

    /**
     * @param string $resiId
     */
    public function setResiId($resiId)
    {
        $this->resiId = $resiId;
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
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param int $errorCode
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @return string
     */
    public function getErrorDescription()
    {
        return $this->errorDescription;
    }

    /**
     * @param string $errorDescription
     */
    public function setErrorDescription($errorDescription)
    {
        $this->errorDescription = $errorDescription;
    }

    /**
     * @return string
     */
    public function getEvolutionReference()
    {
        return $this->evolutionReference;
    }

    /**
     * @param string $evolutionReference
     */
    public function setEvolutionReference($evolutionReference)
    {
        $this->evolutionReference = $evolutionReference;
    }

    /**
     * @return string
     */
    public function getEvolutionReferenceDescription()
    {
        return $this->evolutionReferenceDescription;
    }

    /**
     * @param string $evolutionReferenceDescription
     */
    public function setEvolutionReferenceDescription($evolutionReferenceDescription)
    {
        $this->evolutionReferenceDescription = $evolutionReferenceDescription;
    }

    /**
     * @return AdditionalData
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    /**
     * @param AdditionalData $additionalData
     */
    public function setAdditionalData($additionalData)
    {
        $this->additionalData = $additionalData;
    }

    /**
     * @return string
     */
    public function getChargeDateFrom()
    {
        return $this->chargeDateFrom;
    }

    /**
     * @param string $chargeDateFrom
     */
    public function setChargeDateFrom($chargeDateFrom)
    {
        $this->chargeDateFrom = $chargeDateFrom;
    }

    /**
     * @return string
     */
    public function getChargeDateThru()
    {
        return $this->chargeDateThru;
    }

    /**
     * @param string $chargeDateThru
     */
    public function setChargeDateThru($chargeDateThru)
    {
        $this->chargeDateThru = $chargeDateThru;
    }

    /**
     * @return int
     */
    public function getChargeIncCode()
    {
        return $this->chargeIncCode;
    }

    /**
     * @param int $chargeIncCode
     */
    public function setChargeIncCode($chargeIncCode)
    {
        $this->chargeIncCode = $chargeIncCode;
    }

    /**
     * @return int
     */
    public function getCheckNo()
    {
        return $this->checkNo;
    }

    /**
     * @param int $checkNo
     */
    public function setCheckNo($checkNo)
    {
        $this->checkNo = $checkNo;
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
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @param string $paymentType
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
    }
}
