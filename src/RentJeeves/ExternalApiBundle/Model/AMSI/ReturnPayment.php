<?php

namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

class ReturnPayment
{
    /**
     * @Serializer\SerializedName("ClientTransactionID")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"returnPayment", "returnPaymentResponse"})
     * @Serializer\Type("integer")
     *
     * @var integer
     */
    protected $clientTransactionId;

    /**
     * @Serializer\SerializedName("Reason")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"returnPayment", "returnPaymentResponse"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $reason;

    /**
     * @Serializer\SerializedName("ClientJnlNo")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"returnPayment", "returnPaymentResponse"})
     * @Serializer\Type("integer")
     *
     * @var integer
     */
    protected $clientJnlNo;

    /**
     * @Serializer\SerializedName("Description")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"returnPayment"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $description;

    /**
     * @Serializer\SerializedName("EvolutionTransactionID")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"returnPaymentResponse"})
     * @Serializer\Type("integer")
     *
     * @var integer
     */
    protected $evolutionTransactionId;

    /**
     * @Serializer\SerializedName("ErrorCode")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"returnPaymentResponse"})
     * @Serializer\Type("integer")
     *
     * @var integer
     */
    protected $errorCode;

    /**
     * @Serializer\SerializedName("ErrorDescription")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"returnPaymentResponse"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $errorDescription;

    /**
     * @Serializer\SerializedName("EvolutionReference")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"returnPaymentResponse"})
     * @Serializer\Type("integer")
     *
     * @var integer
     */
    protected $evolutionReference;

    /**
     * @Serializer\SerializedName("EvolutionReferenceDescription")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"returnPaymentResponse"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $evolutionReferenceDescription;

    /**
     * @Serializer\SerializedName("xmlGL")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"returnPaymentResponse"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $xmlGl;

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
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
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
        $this->description = substr($description, 0, 30);
    }

    /**
     * @return int
     */
    public function getEvolutionTransactionId()
    {
        return $this->evolutionTransactionId;
    }

    /**
     * @param int $evolutionTransactionId
     */
    public function setEvolutionTransactionId($evolutionTransactionId)
    {
        $this->evolutionTransactionId = $evolutionTransactionId;
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
     * @return int
     */
    public function getEvolutionReference()
    {
        return $this->evolutionReference;
    }

    /**
     * @param int $evolutionReference
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
     * @return string
     */
    public function getXmlGl()
    {
        return $this->xmlGl;
    }

    /**
     * @param string $xmlGl
     */
    public function setXmlGl($xmlGl)
    {
        $this->xmlGl = $xmlGl;
    }
}
