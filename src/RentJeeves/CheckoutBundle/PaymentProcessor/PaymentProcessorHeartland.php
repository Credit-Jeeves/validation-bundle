<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\ChargeHeartland;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\PayHeartland;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\ReportLoader;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as AccountData;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\GroupAwareInterface;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\PaymentAccountManager;
use RentJeeves\DataBundle\Enum\PaymentGroundType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

/**
 * @DI\Service("payment_processor.heartland")
 */
class PaymentProcessorHeartland implements PaymentProcessorInterface
{
    /** @var PaymentAccountManager */
    protected $paymentAccountManager;

    /** @var PayHeartland */
    protected $paymentManager;

    /** @var  ChargeHeartland */
    protected $chargeManager;

    /** @var ReportLoader */
    protected $reportLoader;

    /**
     * @param PaymentAccountManager $paymentAccountManager
     * @param PayHeartland $paymentManager
     * @param ChargeHeartland $chargeManager
     * @param ReportLoader $reportLoader
     *
     * @DI\InjectParams({
     *     "paymentAccountManager" = @DI\Inject("payment.account.heartland"),
     *     "paymentManager" = @DI\Inject("payment.pay_heartland"),
     *     "chargeManager" = @DI\Inject("payment.charge_heartland"),
     *     "reportLoader" = @DI\Inject("payment_processor.heartland.report_loader")
     * })
     */
    public function __construct(
        PaymentAccountManager $paymentAccountManager,
        PayHeartland $paymentManager,
        ChargeHeartland $chargeManager,
        ReportLoader $reportLoader
    ) {
        $this->paymentAccountManager = $paymentAccountManager;
        $this->paymentManager = $paymentManager;
        $this->chargeManager = $chargeManager;
        $this->reportLoader = $reportLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function createPaymentToken(AccountData $paymentAccountData, Contract $contract)
    {
        $group = $contract->getGroup();

        $user = $contract->getTenant();

        return $this->paymentAccountManager->getToken($paymentAccountData, $user, $group);
    }

    /**
     * {@inheritdoc}
     */
    public function createBillingToken(AccountData $billingAccountData, Landlord $user)
    {
        if (!$billingAccountData->getEntity() || !$billingAccountData->getEntity() instanceof GroupAwareInterface) {
            throw new PaymentProcessorInvalidArgumentException(
                'createBillingToken should use entity implemented GroupAwareInterface'
            );
        }

        return $this->paymentAccountManager->getToken($billingAccountData, $user);
    }

    /**
     * {@inheritdoc}
     */
    public function executeOrder(
        Order $order,
        PaymentAccountInterface $accountEntity,
        $paymentType = PaymentGroundType::RENT
    ) {
        if (PaymentGroundType::CHARGE !== $paymentType && !$this->isAllowedToExecuteOrder($order, $accountEntity)) {
            throw PaymentProcessorInvalidArgumentException::invalidPaymentProcessor(
                PaymentProcessor::HEARTLAND
            );
        } elseif (PaymentGroundType::CHARGE === $paymentType) {
            return $this->chargeManager->executePayment($order, $accountEntity);
        }

        return $this->paymentManager->executePayment($order, $accountEntity, $paymentType);
    }

    /**
     * {@inheritdoc}
     */
    public function loadReport()
    {
        return $this->reportLoader->loadReport();
    }

    /**
     * @param  Order $order
     * @param  PaymentAccountInterface $paymentAccount
     * @return bool
     */
    protected function isAllowedToExecuteOrder(Order $order, PaymentAccountInterface $paymentAccount)
    {
        if ($paymentAccount instanceof PaymentAccount &&
            $order->getPaymentProcessor() === PaymentProcessor::HEARTLAND &&
            $order->getPaymentProcessor() === $paymentAccount->getPaymentProcessor()
        ) {
            return true;
        }

        return false;
    }
}
