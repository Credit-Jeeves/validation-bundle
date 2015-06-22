<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("BATCH")
 */
class Batch
{
    /**
     * @Serializer\Type(
     *     "ArrayCollection<RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response\Payment>"
     * )
     * @Serializer\XmlList(inline = true, entry="PI")
     * @Serializer\XmlKeyValuePairs
     */
    protected $payments;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("BATCHID")
     */
    protected $batchId;

    /**
     * @return mixed
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param mixed $payments
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;
    }

    /**
     * @return mixed
     */
    public function getBatchId()
    {
        return $this->batchId;
    }

    /**
     * @param mixed $batchId
     */
    public function setBatchId($batchId)
    {
        $this->batchId = $batchId;
    }
}
