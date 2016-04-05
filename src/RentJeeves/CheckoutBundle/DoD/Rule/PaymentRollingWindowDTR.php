<?php

namespace RentJeeves\CheckoutBundle\DoD\Rule;

use CreditJeeves\DataBundle\Entity\OrderRepository;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentFlaggedReason;

/**
 * Service name "dod.payment_rolling_window_dtr"
 */
class PaymentRollingWindowDTR implements DodPaymentRuleInterface
{
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var int
     */
    protected $rollingWindow;

    /**
     * @param OrderRepository $orderRepository
     * @param int $rollingWindow
     */
    public function __construct(OrderRepository $orderRepository, $rollingWindow)
    {
        $this->orderRepository = $orderRepository;
        $this->rollingWindow = (int) $rollingWindow;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPayment(Payment $payment)
    {
        $contract = $payment->getContract();
        if ($contract->getGroup()->getOrderAlgorithm() === OrderAlgorithmType::PAYDIRECT &&
            $lastDTROrder = $this->orderRepository->getLastPaidOrderByContract($contract)
        ) {
            $minStartDate = clone $lastDTROrder->getCreatedAt();
            $minStartDate->modify('+' . $this->rollingWindow . ' days');

            return $payment->getStartDate() > $minStartDate;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonMessage()
    {
        return 'Payment date should be inside rolling window.';
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonCode()
    {
        return PaymentFlaggedReason::OUTSIDE_DTR_ROLLING_WINDOW;
    }

    /**
     * {@inheritdoc}
     */
    public function support($object)
    {
        return $object instanceof Payment;
    }
}
