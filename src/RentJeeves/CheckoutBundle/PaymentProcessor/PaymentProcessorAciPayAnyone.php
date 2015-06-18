<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;

class PaymentProcessorAciPayAnyone implements PayDirectProcessorInterface
{

    /**
     * {@inheritdoc}
     */
    public function executeOrder(Order $order)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function cancelOrder(Order $order)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function loadReport()
    {

    }
}
