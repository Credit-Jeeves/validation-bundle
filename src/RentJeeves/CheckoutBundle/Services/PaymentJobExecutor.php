<?php

namespace RentJeeves\CheckoutBundle\Services;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use RentJeeves\CheckoutBundle\DoD\DodManager;
use RentJeeves\CheckoutBundle\Payment\PayCreditTrack;
use RentJeeves\CheckoutBundle\Payment\PayRent;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\JobRelatedCreditTrack;
use RentJeeves\DataBundle\Entity\JobRelatedPayment;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Enum\DepositAccountType;

/**
 * @DI\Service("checkout.payment_job_executor")
 */
class PaymentJobExecutor
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PayRent
     */
    protected $payRent;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var PayCreditTrack
     */
    protected $payCreditTrack;

    /**
     * @var DodManager
     */
    protected $dodManager;

    /**
     * @var Job
     */
    protected $job;

    /**
     * @var int
     */
    protected $exitCode = 0;

    /**
     * @var string
     */
    protected $message = 'OK';

    /**
     * @var string
     */
    protected $creditSummaryVendor;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "payRent" = @DI\Inject("payment.pay_rent"),
     *     "payCreditTrack" = @DI\Inject("payment.pay_credit_track"),
     *     "logger" = @DI\Inject("logger"),
     *     "dodManager" = @DI\Inject("dod"),
     *     "creditSummaryVendor" = @DI\Inject("%credit_summary_vendor%")
     * })
     *
     * @param EntityManager $em
     * @param PayRent $payRent
     * @param PayCreditTrack $payCreditTrack
     * @param Logger $logger
     * @param DodManager $dodManager
     * @param string $creditSummaryVendor
     */
    public function __construct(
        EntityManager $em,
        PayRent $payRent,
        PayCreditTrack $payCreditTrack,
        Logger $logger,
        DodManager $dodManager,
        $creditSummaryVendor
    ) {
        $this->em = $em;
        $this->payRent = $payRent;
        $this->payCreditTrack = $payCreditTrack;
        $this->logger = $logger;
        $this->creditSummaryVendor = $creditSummaryVendor;
        $this->dodManager = $dodManager;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getExitCode()
    {
        return $this->exitCode;
    }

    /**
     * @param Job $job
     *
     * @return bool
     */
    public function execute(Job $job)
    {
        $this->job = $job;
        foreach ($this->job->getRelatedEntities() as $relatedEntity) {
            switch (true) {
                case $relatedEntity instanceof JobRelatedPayment:
                    return $this->executePayment($relatedEntity->getPayment());
                    break;
                case $relatedEntity instanceof JobRelatedCreditTrack:
                    return $this->executeCreditTrack($relatedEntity->getCreditTrackPaymentAccount());
                    break;
            }
        }
        $this->message = sprintf("Job ID:'%s' must have related payment", $job->getId());
        $this->logger->debug('Related entity for job ID ' . $job->getId() .' not found');

        return false;
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    protected function processStatus(Order $order)
    {
        if (OrderStatus::ERROR == $order->getStatus()) {
            $this->message = $order->getErrorMessage();
            $this->exitCode = 1;

            return false;
        }

        return true;
    }

    /**
     * @param Payment $payment
     *
     * @return bool
     */
    protected function executePayment(Payment $payment)
    {
        $this->logger->debug('Starting execute rent payment ID ' . $payment->getId());

        $date = new DateTime();
        $contract = $payment->getContract();

        $filterClosure = function (Operation $operation) use ($date) {
            if (($operation->getType() === OperationType::RENT) &&
                ($order = $operation->getOrder()) &&
                $order->getCreatedAt()->format('Y-m-d') == $date->format('Y-m-d') &&
                $order->getPaymentType() != OrderPaymentType::CASH &&
                OrderStatus::ERROR != $order->getStatus() &&
                OrderStatus::CANCELLED != $order->getStatus()
            ) {
                return true;
            }

            return false;
        };
        if (DepositAccountType::RENT === $payment->getDepositAccount()->getType() &&
            !$contract->getOperations()->filter($filterClosure)->isEmpty()
        ) {
            $this->message = 'Payment already executed.';
            $this->exitCode = 1;
            $this->logger->debug('Payment already executed. Payment ID ' . $payment->getId());

            return false;
        }

        if (!$this->dodManager->checkPayment($payment)) {
            $this->message = 'Dod checking is failed.';
            $this->exitCode = 1;
            $this->logger->debug('Dod checking is failed. Payment ID ' . $payment->getId());
            $this->em->flush($payment);

            return false;
        }

        $order = $this->payRent->executePayment($payment);

        $this->logger->debug('Add created order to job related entities. Job ID ' . $this->job->getId());
        $this->job->addRelatedEntity($order);
        $this->em->persist($this->job);
        $this->em->flush();
        $this->em->clear();

        return $this->processStatus($order);
    }

    /**
     * @param PaymentAccount $paymentAccount
     *
     * @return bool
     */
    protected function executeCreditTrack(PaymentAccount $paymentAccount)
    {
        $order = $this->payCreditTrack->executePaymentAccount($paymentAccount);
        $this->job->addRelatedEntity($order);
        $this->em->persist($this->job);

        $isSuccessful = $this->processStatus($order);

        if ($isSuccessful) {
            /** @var Operation $operation */
            $operation = $order->getOperations()->last();
            $job = new Job('score-track:get-report');
            $job->addRelatedEntity($operation->getReportByVendor($this->creditSummaryVendor));
            $this->em->persist($job);
        }

        $this->em->flush();

        return $isSuccessful;
    }
}
