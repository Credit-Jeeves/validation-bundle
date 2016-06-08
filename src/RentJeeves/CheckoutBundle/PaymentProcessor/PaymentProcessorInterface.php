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

    /**
     * Returns the number of business days required for processor to deliver the given payment type.
     *
     * @param string $paymentType is of type CreditJeeves\DataBundle\Enum\OrderPaymentType
     *
     * @return int the required number of business days until deposit/delivery
     */
    public function getBusinessDaysRequired($paymentType);

    /**
     * @param string $paymentType is of type CreditJeeves\DataBundle\Enum\OrderPaymentType
     * @param \DateTime $executeDate
     *
     * @return \DateTime the estimated deposit date
     */
    public function calculateDepositDate($paymentType, \DateTime $executeDate);
}
