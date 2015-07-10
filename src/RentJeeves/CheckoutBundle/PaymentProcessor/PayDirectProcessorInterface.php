<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\OrderPayDirect;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;

interface PayDirectProcessorInterface
{
    /**
     * Executes PayDirect order.
     * Returns transaction id.
     *
     * @param OrderPayDirect $order
     *
     * @return string Order status
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

    /**
     * Loads payment processor report.
     *
     * @return PaymentProcessorReport
     */
    public function loadReport();
}
