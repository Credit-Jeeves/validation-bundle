<?php

namespace RentJeeves\CheckoutBundle\DoD\Rule;

use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentFlaggedReason;

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
     * {@inheritdoc}
     */
    public function checkPayment(Payment $payment)
    {
        if ($payment->getContract()->getGroup()->getOrderAlgorithm() === OrderAlgorithmType::PAYDIRECT &&
            $payment->getTotal() > $this->paymentMaxLimit
        ) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonMessage()
    {
        return sprintf('Payment amount exceeds MAX limit of %s', $this->paymentMaxLimit);
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonCode()
    {
        return PaymentFlaggedReason::AMOUNT_LIMIT_EXCEEDED;
    }

    /**
     * {@inheritdoc}
     */
    public function support($object)
    {
        return $object instanceof Payment;
    }
}
