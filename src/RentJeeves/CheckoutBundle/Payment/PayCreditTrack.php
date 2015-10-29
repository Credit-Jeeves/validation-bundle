<?php
namespace RentJeeves\CheckoutBundle\Payment;

use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Entity\ReportTransunionSnapshot;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManager;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderCreationManager\OrderCreationManager;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderStatusManagerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\PayDirectProcessorInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorFactory;
use RentJeeves\CheckoutBundle\PaymentProcessor\SubmerchantProcessorInterface;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\CreditSummaryVendor;
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
     * @var string
     */
    protected $creditSummaryVendor;

    /**
     * @param OrderCreationManager $orderCreationManager
     * @param OrderStatusManagerInterface $orderStatusManager
     * @param EntityManager $em
     * @param string $creditSummaryVendor
     */
    public function __construct(
        OrderCreationManager $orderCreationManager,
        OrderStatusManagerInterface $orderStatusManager,
        EntityManager $em,
        $creditSummaryVendor
    ) {
        $this->orderCreationManager = $orderCreationManager;
        $this->orderStatusManager = $orderStatusManager;
        $this->em = $em;
        $this->creditSummaryVendor = $creditSummaryVendor;
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

        if (OrderStatus::COMPLETE === $order->getStatus()) {
            $report = $this->createReport($paymentAccount->getUser());
            $order->getOperations()->last()->setReport($report);

            $this->em->persist($report);
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
        return $this->paymentProcessorFactory->getPaymentProcessorByPaymentAccount($paymentAccount);
    }

    /**
     * Creates a new report.
     *
     * @param  User $user
     * @return ReportPrequal|ReportTransunionSnapshot
     * @throws \Exception
     */
    protected function createReport(User $user)
    {
        switch ($this->creditSummaryVendor) {
            case CreditSummaryVendor::TRANSUNION:
                $report = new ReportTransunionSnapshot();
                break;
            case CreditSummaryVendor::EXPERIAN:
                $report = new ReportPrequal();
                break;
            default:
                throw new \Exception(sprintf('Unsupported credit summary vendor "%s"', $this->creditSummaryVendor));
        }
        $report->setUser($user);
        $report->setRawData('');

        return $report;
    }
}
