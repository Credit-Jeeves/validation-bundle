<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;

interface PaymentProcessorInterface
{
    /**
     * Returns the name of payment processor.
     *
     * @return string
     */
    public function getName();

    /**
     * Loads payment processor report.
     *
     * @return PaymentProcessorReport
     */
    public function loadReport();

    /**
     * Generates reversed batch id for given order.
     *
     * @param Order $order
     * @return string
     */
    public function generateReversedBatchId(Order $order);
}
