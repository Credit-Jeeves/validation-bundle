<?php

namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("EDEX")
 */
class EdexSettlement
{
    /**
     * @Serializer\SerializedName("ExternalJnlNo")
     * @Serializer\Type("integer")
     * @Serializer\Groups({"updateSettlementData", "updateSettlementDataResponse"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @var int
     */
    protected $externalJnlNo;

    /**
     * @Serializer\SerializedName("ClientMerchantId")
     * @Serializer\Type("integer")
     * @Serializer\Groups({"updateSettlementData", "updateSettlementDataResponse"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @var int
     */
    protected $clientMerchantId;

    /**
     * @Serializer\SerializedName("SettlementAmount")
     * @Serializer\Type("double")
     * @Serializer\Groups({"updateSettlementData", "updateSettlementDataResponse"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @var double
     */
    protected $settlementAmount;

    /**
     * @Serializer\SerializedName("SettlementDate")
     * @Serializer\Type("DateTime<'m/d/Y'>")
     * @Serializer\Groups({"updateSettlementData", "updateSettlementDataResponse"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @var \DateTime
     */
    protected $settlementDate;

    /**
     * @Serializer\SerializedName("ApprovalCode")
     * @Serializer\Type("string")
     * @Serializer\Groups({"updateSettlementData", "updateSettlementDataResponse"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @var string
     */
    protected $approvalCode;

    /**
     * @Serializer\SerializedName("ErrorCode")
     * @Serializer\Type("integer")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"updateSettlementDataResponse"})
     *
     * @var int
     */
    protected $errorCode;

    /**
     * @Serializer\SerializedName("ErrorDescription")
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"updateSettlementDataResponse"})
     *
     * @var string
     */
    protected $errorDescription;

    /**
     * @Serializer\SerializedName("EvolutionReference")
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"updateSettlementDataResponse"})
     *
     * @var string
     */
    protected $evolutionReference;

    /**
     * @Serializer\SerializedName("EvolutionReferenceDescription")
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"updateSettlementDataResponse"})
     *
     * @var string
     */
    protected $evolutionReferenceDescription;

    /**
     * @return string
     */
    public function getApprovalCode()
    {
        return $this->approvalCode;
    }

    /**
     * @param string $approvalCode
     */
    public function setApprovalCode($approvalCode)
    {
        $this->approvalCode = $approvalCode;
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
     * @return int
     */
    public function getExternalJnlNo()
    {
        return $this->externalJnlNo;
    }

    /**
     * @param int $externalJnlNo
     */
    public function setExternalJnlNo($externalJnlNo)
    {
        $this->externalJnlNo = $externalJnlNo;
    }

    /**
     * @return float
     */
    public function getSettlementAmount()
    {
        return $this->settlementAmount;
    }

    /**
     * @param float $settlementAmount
     */
    public function setSettlementAmount($settlementAmount)
    {
        $this->settlementAmount = $settlementAmount;
    }

    /**
     * @return \DateTime
     */
    public function getSettlementDate()
    {
        return $this->settlementDate;
    }

    /**
     * @param \DateTime $settlementDate
     */
    public function setSettlementDate($settlementDate)
    {
        $this->settlementDate = $settlementDate;
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
