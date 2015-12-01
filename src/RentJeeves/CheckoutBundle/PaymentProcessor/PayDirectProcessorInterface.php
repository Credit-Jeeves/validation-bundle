<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\OrderPayDirect;

interface PayDirectProcessorInterface extends PaymentProcessorInterface
{
    /**
     * Executes PayDirect order.
     *
     * @param OrderPayDirect $order
     *
     * @return bool
     */
    public function executeOrder(OrderPayDirect $order);

    /**
     * Cancels PayDirect order.
     *
     * @param OrderPayDirect $order
     *
     * @return bool
     */
    public function cancelOrder(OrderPayDirect $order);
}
