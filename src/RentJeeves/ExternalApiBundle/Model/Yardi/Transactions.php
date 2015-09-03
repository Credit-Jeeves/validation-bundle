<?php

namespace RentJeeves\ExternalApiBundle\Model\Yardi;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\YardiSettings;

class Transactions
{
    /**
     * @Serializer\SerializedName("Payment")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\Yardi\Payment")
     * @Serializer\Groups({"baseRequest", "withPostMonth", "reversedPayment"})
     */
    protected $payment;

    public function __construct(YardiSettings $yardiSettings, Order $order = null)
    {
        $this->payment = new Payment($yardiSettings, $order);
    }

    /**
     * @param Payment $payment
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }
}
