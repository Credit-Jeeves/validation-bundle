<?php

namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Payments")
 */
class ReturnPayments
{
    /**
     * @Serializer\Type("ArrayCollection<RentJeeves\ExternalApiBundle\Model\AMSI\ReturnPayment>")
     * @Serializer\XmlList(inline = true, entry = "Payment")
     * @Serializer\Groups({"returnPayment" , "returnPaymentResponse"})
     * @Serializer\XmlKeyValuePairs
     *
     * @var ArrayCollection
     */
    protected $payments;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param ReturnPayment $payment
     */
    public function addPayment(ReturnPayment $payment)
    {
        $this->payments->add($payment);
    }
}
