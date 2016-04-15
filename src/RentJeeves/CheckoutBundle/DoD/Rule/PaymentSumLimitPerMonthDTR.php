<?php

namespace RentJeeves\CheckoutBundle\DoD\Rule;

use CreditJeeves\DataBundle\Entity\OperationRepository;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentFlaggedReason;

/**
 * Service name "dod.payment_sum_limit_per_month_dtr"
 */
class PaymentSumLimitPerMonthDTR implements DodPaymentRuleInterface
{
    /**
     * @var OperationRepository
     */
    protected $operationRepository;

    /**
     * @param OperationRepository $operationRepository
     */
    public function __construct(OperationRepository $operationRepository)
    {
        $this->operationRepository = $operationRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPayment(Payment $payment)
    {
        $group = $payment->getContract()->getGroup();

        if ($group->getOrderAlgorithm() === OrderAlgorithmType::PAYDIRECT &&
            $group->getGroupSettings()->getMaxLimitPerMonth() > 0 &&
            $this->operationRepository->getSumPaymentsByGroupInDateMonth(
                $group,
                $payment->getStartDate() ?: new \DateTime()
            ) + $payment->getTotal() > $group->getGroupSettings()->getMaxLimitPerMonth()
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
        return 'Max limit per month is over.';
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonCode()
    {
        return PaymentFlaggedReason::DTR_MONTH_LIMIT_OVERFLOWED;
    }

    /**
     * {@inheritdoc}
     */
    public function support($object)
    {
        return $object instanceof Payment;
    }
}
