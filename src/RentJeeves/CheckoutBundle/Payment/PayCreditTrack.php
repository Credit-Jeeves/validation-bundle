<?php
namespace RentJeeves\CheckoutBundle\Payment;

use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\DataBundle\Entity\ReportD2c;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\EntityManager;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderCreationManager\OrderCreationManager;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderStatusManagerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\PayDirectProcessorInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorFactory;
use RentJeeves\CheckoutBundle\PaymentProcessor\SubmerchantProcessorInterface;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

class PayCreditTrack
{
    /**
     * @var OrderCreationManager
     */
    protected $orderCreationManager;

    /**
     * @var OrderStatusManagerInterface
     */
    protected $orderStatusManager;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PaymentProcessorFactory
     */
    protected $paymentProcessorFactory;

    /**
     * @param OrderCreationManager $orderCreationManager
     * @param OrderStatusManagerInterface $orderStatusManager
     * @param EntityManager $em
     */
    public function __construct(
        OrderCreationManager $orderCreationManager,
        OrderStatusManagerInterface $orderStatusManager,
        EntityManager $em
    ) {
        $this->orderCreationManager = $orderCreationManager;
        $this->orderStatusManager = $orderStatusManager;
        $this->em = $em;
    }

    /**
     * @param PaymentProcessorFactory $factory
     *
     * Setter injection is used b/c PaymentProcessorFactory doesn't exist when __construct is called.
     */
    public function setFactory(PaymentProcessorFactory $factory)
    {
        $this->paymentProcessorFactory = $factory;
    }

    /**
     * Runs CreditTrack payment using paymentAccount.
     *
     * @param  PaymentAccount $paymentAccount
     * @return OrderSubmerchant
     */
    public function executePaymentAccount(PaymentAccount $paymentAccount)
    {
        $order = $this->orderCreationManager->createCreditTrackOrder($paymentAccount);

        $this->orderStatusManager->setNew($order);

        try {
            if ($this->getPaymentProcessor($paymentAccount)->executeOrder(
                $order,
                $paymentAccount,
                PaymentGroundType::REPORT
            )) {
                $this->orderStatusManager->setComplete($order);
            } else {
                $this->orderStatusManager->setError($order);
            }
        } catch (\Exception $e) {
            $this->orderStatusManager->setError($order);
        }

        if (OrderStatus::ERROR != $order->getStatus()) {
            $report = $this->createReport($paymentAccount->getUser());
            $order->getOperations()->last()->setReportD2c($report);
            $job = $this->scheduleReportJob($report);

            $this->em->persist($report);
            $this->em->persist($job);
        }

        $this->em->flush();

        return $order;
    }

    /**
     * Finds payment processor for a given payment account.
     *
     * @param  PaymentAccount                                            $paymentAccount
     * @return SubmerchantProcessorInterface|PayDirectProcessorInterface
     */
    protected function getPaymentProcessor(PaymentAccount $paymentAccount)
    {
        $group = $paymentAccount->getDepositAccounts()->first()->getGroup();

        return $this->paymentProcessorFactory->getPaymentProcessor($group);
    }

    /**
     * Creates a new D2C report.
     *
     * @param  User      $user
     * @return ReportD2c
     */
    protected function createReport(User $user)
    {
        $report = new ReportD2c();
        $report->setUser($user);
        $report->setRawData('');

        return $report;
    }

    /**
     * Creates a job to load report.
     * TODO: change job command to be dependent on the credit_summary_vendor config setting
     *
     * @param Report $report
     * @return Job
     */
    protected function scheduleReportJob(Report $report)
    {
        $job = new Job('experian-credit_profile:get', ['--app=rj']);
        $job->addRelatedEntity($report);
        $execute = new DateTime();
        $execute->modify("+5 minutes");
        $job->setExecuteAfter($execute);

        return $job;
    }
}
