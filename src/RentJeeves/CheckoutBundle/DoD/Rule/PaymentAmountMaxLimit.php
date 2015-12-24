<?php

namespace RentJeeves\CheckoutBundle\DoD\Rule;

use RentJeeves\DataBundle\Entity\Payment;

/**
 * Service name "dod.payment_amount_max"
 */
class PaymentAmountMaxLimit implements DodRuleInterface
{
    /**
     * @var int
     */
    protected $paymentMaxLimit;

    /**
     * @param int $limit
     */
    public function __construct($limit)
    {
        $this->paymentMaxLimit = $limit;
    }

    /**
     * @param Payment $payment
     * @return bool
     */
    public function checkPayment(Payment $payment)
    {
        if ($payment->getTotal() > $this->paymentMaxLimit) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getReason()
    {
        return sprintf('Payment amount exceeds MAX limit of %s', $this->paymentMaxLimit);
    }
}
