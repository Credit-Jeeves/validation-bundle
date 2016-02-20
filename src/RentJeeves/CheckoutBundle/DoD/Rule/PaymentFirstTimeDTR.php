<?php

namespace RentJeeves\CheckoutBundle\DoD\Rule;

use CreditJeeves\DataBundle\Entity\OrderRepository;
use CreditJeeves\DataBundle\Entity\PidkiqRepository;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentRepository;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;

/**
 * Service name "dod.payment_first_time_dtr"
 */
class PaymentFirstTimeDTR implements DodPaymentRuleInterface
{
    /**
     * @var PidkiqRepository
     */
    protected $pidkiqRepository;

    /**
     * @var PaymentRepository
     */
    protected $paymentRepository;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var Payment
     */
    protected $currentPayment;

    /**
     * @param PidkiqRepository $pidkiqRepository
     * @param PaymentRepository $paymentRepository
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        PidkiqRepository $pidkiqRepository,
        PaymentRepository $paymentRepository,
        OrderRepository $orderRepository
    ) {
        $this->pidkiqRepository = $pidkiqRepository;
        $this->paymentRepository = $paymentRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPayment(Payment $payment)
    {
        $this->currentPayment = $payment;
        $contract = $payment->getContract();

        if ($contract->getGroup()->getOrderAlgorithm() !== OrderAlgorithmType::PAYDIRECT) {
            return true;
        }

        if (!$this->paymentRepository->countPaymentsByContract($contract) &&
            !$this->orderRepository->countOrdersByContract($contract)
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
        if (!$this->currentPayment) {
            return '';
        }

        $tenant = $this->currentPayment->getContract()->getTenant();
        $pidkiqSession = $this->pidkiqRepository->findLastSuccessSessionByUser($tenant);

        return sprintf(
            'First time payment for this tenant (%s %s%s) needs review. Date of PIDKIQ validation: %s',
            $tenant->getFirstName(),
            $tenant->getLastName(),
            $tenant->getPhone() ? ', phone: ' . $tenant->getFormattedPhone() : '',
            $pidkiqSession ? $pidkiqSession->getUpdatedAt()->format('Y-m-d') : ''
        );
    }

    /**
     * {@inheritdoc}
     */
    public function support($object)
    {
        return $object instanceof Payment;
    }
}
