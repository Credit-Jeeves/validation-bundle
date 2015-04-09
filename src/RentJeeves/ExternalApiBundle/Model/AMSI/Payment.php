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
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $clientMerchantId;

    /**
     * @Serializer\SerializedName("ClientTransactionID")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     * @Serializer\Type("string")
     *
     * @var string
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
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $amount;

    /**
     * @Serializer\SerializedName("ClientJnlNo")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     * @Serializer\Type("string")
     *
     * @var string
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
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     *
     * @var string
     */
    protected $checkNo = '';

    /**
     * @Serializer\SerializedName("ChargeIncCode")
     * @Serializer\Type("string")
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
     * @Serializer\Type("string")
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
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $amount
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
     * @return string
     */
    public function getClientJnlNo()
    {
        return $this->clientJnlNo;
    }

    /**
     * @param string $clientJnlNo
     */
    public function setClientJnlNo($clientJnlNo)
    {
        $this->clientJnlNo = $clientJnlNo;
    }

    /**
     * @return string
     */
    public function getClientMerchantId()
    {
        return $this->clientMerchantId;
    }

    /**
     * @param string $clientMerchantId
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
     * @return string
     */
    public function getClientTransactionId()
    {
        return $this->clientTransactionId;
    }

    /**
     * @param string $clientTransactionId
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
}
