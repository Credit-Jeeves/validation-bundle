<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan\Transaction;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\Serializer\Annotation as Serializer;

class Transactions
{
    /**
     * @Serializer\SerializedName("Payment")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\Transaction\Payment")
     * @Serializer\Groups({"ResMan"})
     */
    protected $payment;

    public function __construct(Order $order = null)
    {
        $this->payment = new Payment($order);
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
