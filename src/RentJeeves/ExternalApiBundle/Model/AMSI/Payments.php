<?php

namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Payments")
 */
class Payments
{
    /**
     * @Serializer\SerializedName("Payment")
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Model\AMSI\Payment>")
     * @Serializer\XmlList(inline = true, entry = "Payment")
     * @Serializer\XmlKeyValuePairs
     * @Serializer\Groups({"addPayment", "addPaymentResponse"})
     *
     * @var array<Payment>
     */
    protected $payments;

    /**
     * @return array
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param array $payments
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;
    }
}
