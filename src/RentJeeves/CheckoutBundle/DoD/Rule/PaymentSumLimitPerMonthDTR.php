<?php

namespace RentJeeves\CheckoutBundle\DoD\Rule;

use CreditJeeves\DataBundle\Entity\OperationRepository;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;

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
            ) > $group->getGroupSettings()->getMaxLimitPerMonth()
        ) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getReason()
    {
        return 'Max limit per month is over.';
    }

    /**
     * {@inheritdoc}
     */
    public function support($object)
    {
        return $object instanceof Payment;
    }
}
