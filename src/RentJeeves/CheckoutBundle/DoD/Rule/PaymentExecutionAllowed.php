<?php

namespace RentJeeves\CheckoutBundle\DoD\Rule;

use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\DataBundle\Enum\PaymentFlaggedReason;

/**
 * Service name "dod.payment_execution_allowed"
 */
class PaymentExecutionAllowed implements DodPaymentRuleInterface
{

    /**
     * {@inheritdoc}
     */
    public function checkPayment(Payment $payment)
    {
        $contract = $payment->getContract();
        if ($contract->getGroupSettings()->getIsIntegrated()) {
            return $contract->isPaymentAllowed() && PaymentAccepted::ANY == $contract->getPaymentAccepted();
        }

        return $contract->isPaymentAllowed();
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonMessage()
    {
        return 'Execution of payment is disallowed for this contract';
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonCode()
    {
        return PaymentFlaggedReason::EXECUTION_DISALLOWED;
    }

    /**
     * {@inheritdoc}
     */
    public function support($object)
    {
        return $object instanceof Payment;
    }
}
