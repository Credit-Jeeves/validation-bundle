<?php

namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Payments")
 */
class Payments
{
    /**
     * @Serializer\SerializedName("Payment")
     * @Serializer\Type("ArrayCollection<RentJeeves\ExternalApiBundle\Model\AMSI\Payment>")
     * @Serializer\XmlList(inline = true, entry = "Payment")
     * @Serializer\XmlKeyValuePairs
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
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
     * @param array $payments
     */
    public function addPayment(Payment $payment)
    {
        $this->payments->add($payment);
    }
}
