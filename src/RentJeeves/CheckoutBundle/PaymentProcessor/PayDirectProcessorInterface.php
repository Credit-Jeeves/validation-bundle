<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;

interface PayDirectProcessorInterface
{
    /**
     * Executes PayDirect order.
     * Returns transaction id.
     *
     * @param Order $order
     *
     * @return string Order status
     */
    public function executeOrder(Order $order);

    /**
     * Cancels PayDirect order.
     *
     * @param Order $order
     *
     * @return boolean
     */
    public function cancelOrder(Order $order);

    /**
     * Loads payment processor report.
     *
     * @return PaymentProcessorReport
     */
    public function loadReport();
}
