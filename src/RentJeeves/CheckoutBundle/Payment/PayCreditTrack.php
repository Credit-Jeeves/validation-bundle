<?php
namespace RentJeeves\CheckoutBundle\Payment;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\DataBundle\Entity\ReportD2c;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorFactory;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

/**
 * @DI\Service("payment.pay_credit_track")
 */
class PayCreditTrack
{
    /**
     * @var OrderManager
     */
    protected $orderManager;

    /**
     * @var PaymentProcessorFactory
     */
    protected $paymentProcessorFactory;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @DI\InjectParams({
     *     "orderManager" = @DI\Inject("payment_processor.order_manager"),
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager")
     * })
     */
    public function __construct(OrderManager $orderManager, EntityManager $em)
    {
        $this->orderManager = $orderManager;
        $this->em = $em;
    }

    /**
     * Setter injection is used b/c PaymentProcessorFactory doesn't exist when __construct is called.
     *
     * @DI\InjectParams({"factory" = @DI\Inject("payment_processor.factory")})
     */
    public function setFactory(PaymentProcessorFactory $factory)
    {
        $this->paymentProcessorFactory = $factory;
    }

    /**
     * Runs CreditTrack payment using paymentAccount.
     *
     * @param PaymentAccount $paymentAccount
     * @return Order
     */
    public function executePaymentAccount(PaymentAccount $paymentAccount)
    {
        $order = $this->orderManager->createCreditTrackOrder($paymentAccount);

        $this->em->persist($order);
        $this->em->flush();

        $paymentIsSuccessful = $this->getPaymentProcessor($paymentAccount)->executePayment(
            $order,
            $paymentAccount,
            PaymentGroundType::REPORT
        );

        if ($paymentIsSuccessful) {
            $order->setStatus(OrderStatus::COMPLETE);

            $report = $this->createReport($paymentAccount->getUser());
            $operation = $this->createOperation($order);
            $operation->setReportD2c($report);
            $job = $this->scheduleReportJob($report);

            $this->em->persist($operation);
            $this->em->persist($report);
            $this->em->persist($job);
        } else {
            $order->setStatus(OrderStatus::ERROR);
        }

        $this->em->persist($order);
        $this->em->flush();

        return $order;
    }

    /**
     * Finds payment processor for a given payment account.
     *
     * @param PaymentAccount $paymentAccount
     * @return \RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorInterface
     */
    protected function getPaymentProcessor(PaymentAccount $paymentAccount)
    {
        $group = $paymentAccount->getDepositAccounts()->first()->getGroup();

        return $this->paymentProcessorFactory->getPaymentProcessor($group);
    }

    /**
     * Creates a new D2C report.
     *
     * @param User $user
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
     * Creates a new REPORT type operation for a given order.
     *
     * @param Order $order
     * @return Operation
     */
    protected function createOperation(Order $order)
    {
        $operation = new Operation();
        $operation->setPaidFor(new DateTime());
        $operation->setAmount($order->getSum());
        $operation->setType(OperationType::REPORT);
        $order->addOperation($operation);

        return $operation;
    }

    /**
     * Creates a job to load report.
     * TODO: change job command to be dependent on the credit_summary_vendor config setting
     *
     * @param Report $report
     */
    protected function scheduleReportJob(Report $report)
    {
        $job = new Job('experian-credit_profile:get', array('--app=rj'));
        $job->addRelatedEntity($report);
        $execute = new DateTime();
        $execute->modify("+5 minutes");
        $job->setExecuteAfter($execute);

        return $job;
    }
}
