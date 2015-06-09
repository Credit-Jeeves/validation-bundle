<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("PI")
 */
class Payment
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PAYMENTID")
     */
    protected $transactionId;

    /**
     * @Serializer\Type("double")
     * @Serializer\SerializedName("TRNAMT")
     */
    protected $amount;

    /**
     * @Serializer\Type("DateTime<'ymd'>")
     * @Serializer\SerializedName("DTDUE")
     */
    protected $batchCloseDate;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ERRORCODE")
     */
    protected $reponseCode;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ERRORMESSAGE")
     */
    protected $responseMessage;

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getBatchCloseDate()
    {
        return $this->batchCloseDate;
    }

    /**
     * @param mixed $batchCloseDate
     */
    public function setBatchCloseDate($batchCloseDate)
    {
        $this->batchCloseDate = $batchCloseDate;
    }

    /**
     * @return mixed
     */
    public function getReponseCode()
    {
        return $this->reponseCode;
    }

    /**
     * @param mixed $reponseCode
     */
    public function setReponseCode($reponseCode)
    {
        $this->reponseCode = $reponseCode;
    }

    /**
     * @return mixed
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * @param mixed $responseMessage
     */
    public function setResponseMessage($responseMessage)
    {
        $this->responseMessage = $responseMessage;
    }
}
