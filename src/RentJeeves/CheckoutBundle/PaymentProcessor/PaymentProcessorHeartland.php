<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\PayHeartland;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\ReportLoader;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as PaymentAccountData;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\GroupAwareInterface;
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

    /** @var ReportLoader */
    protected $reportLoader;

    /**
     * @param PaymentAccountManager $paymentAccountManager
     * @param PayHeartland $paymentManager
     * @param ReportLoader $reportLoader
     *
     * @DI\InjectParams({
     *     "paymentAccountManager" = @DI\Inject("payment.account.heartland"),
     *     "paymentManager" = @DI\Inject("payment.pay_heartland"),
     *     "reportLoader" = @DI\Inject("payment_processor.heartland.report_loader")
     * })
     */
    public function __construct(
        PaymentAccountManager $paymentAccountManager,
        PayHeartland $paymentManager,
        ReportLoader $reportLoader
    ) {
        $this->paymentAccountManager = $paymentAccountManager;
        $this->paymentManager = $paymentManager;
        $this->reportLoader = $reportLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function createPaymentAccount(PaymentAccountData $paymentAccountData, Contract $contract)
    {
        $group = $contract->getGroup();

        if ($paymentAccountData->getEntity() instanceof GroupAwareInterface) {
            $user = $paymentAccountData->get('landlord');
        } else {
            $user = $contract->getTenant();
        }

        return $this->paymentAccountManager->getToken($paymentAccountData, $user, $group);
    }

    /**
     * {@inheritdoc}
     */
    public function executeOrder(Order $order, PaymentAccount $paymentAccount, $paymentType = PaymentGroundType::RENT)
    {
        if (!$this->isAllowedToExecuteOrder($order, $paymentAccount)) {
            throw PaymentProcessorInvalidArgumentException::invalidPaymentProcessor(
                PaymentProcessor::HEARTLAND
            );
        }

        return $this->paymentManager->executePayment($order, $paymentAccount, $paymentType);
    }

    /**
     * {@inheritdoc}
     */
    public function loadReport($reportType, array $settings = [])
    {
        return $this->reportLoader->loadReport($reportType, $settings);
    }

    /**
     * @param Order $order
     * @param PaymentAccount $paymentAccount
     * @return bool
     */
    protected function isAllowedToExecuteOrder(Order $order, PaymentAccount $paymentAccount)
    {
        if ($order->getPaymentProcessor() == $paymentAccount->getPaymentProcessor() &&
            $order->getPaymentProcessor() == PaymentProcessor::HEARTLAND
        ) {
            return true;
        }

        return false;
    }
}
