<?php

namespace RentJeeves\CheckoutBundle\DoD;

use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\DoD\Rule\DodPaymentRuleInterface;
use RentJeeves\CheckoutBundle\DoD\Rule\DodRuleInterface;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\PaymentStatus;

/**
 * Service name "dod"
 */
class DodManager
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var array */
    protected $rules = [];

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param DodRuleInterface $rule
     */
    public function addRule(DodRuleInterface $rule)
    {
        $this->rules[] = $rule;
    }

    /**
     * @param Payment $payment
     * @return bool
     */
    public function checkPayment(Payment $payment)
    {
        /** @var DodPaymentRuleInterface $rule */
        foreach ($this->rules as $rule) {
            if ($rule->support($payment)) {
                $isValid = $rule->checkPayment($payment);
                if (!$isValid) {
                    $this->logger->alert(sprintf(
                        'Payment failed DoD checking. Moved to FLAGGED state. Reason: %s . Payment details: ' .
                        'Tenant: %s, Amount: %s, Contract: %s, Type: %s, DepositType: %s.',
                        $rule->getReasonMessage(),
                        $payment->getContract()->getTenant()->getEmail(),
                        $payment->getTotal(),
                        $payment->getContract()->getId(),
                        $payment->getType(),
                        $payment->getDepositAccount()->getType()
                    ));
                    $payment->setStatus(PaymentStatus::FLAGGED);
                    $payment->setFlaggedReason($rule->getReasonCode());

                    return false;
                }
            }
        }

        return true;
    }
}
