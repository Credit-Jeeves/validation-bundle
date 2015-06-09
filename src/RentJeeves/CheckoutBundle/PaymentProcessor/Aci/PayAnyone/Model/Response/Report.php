<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("PAYMENTRESPONSE")
 */
class Report
{
    /**
     * @Serializer\Type(
     *     "ArrayCollection<RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response\Batch>"
     * )
     * @Serializer\XmlList(inline = true, entry="BATCH")
     * @Serializer\XmlKeyValuePairs
     */
    protected $batches;

        /**
     * @return mixed
     */
    public function getBatches()
    {
        return $this->batches;
    }

    /**
     * @param mixed $batches
     */
    public function setBatches($batches)
    {
        $this->batches = $batches;
    }
}
