<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("DETAIL")
 */
class PaymentDetail
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PAYMENT_ID")
     */
    protected $transactionId;

    /**
     * @Serializer\Type("double")
     * @Serializer\SerializedName("AMOUNT")
     */
    protected $amount;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("RETURN_REASON_CODE")
     */
    protected $returnCode;

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param mixed $transactionId
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
    public function getReturnCode()
    {
        return $this->returnCode;
    }

    /**
     * @param mixed $returnCode
     */
    public function setReturnCode($returnCode)
    {
        $this->returnCode = $returnCode;
    }
}
