<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;
use Exception;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\User;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\PayHeartland;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\ReportLoader;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as PaymentAccountData;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\PaymentAccountManager;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

/**
 * @DI\Service("payment_processor.heartland")
 */
class PaymentProcessorHeartland implements PaymentProcessorInterface
{
    /** @var PaymentAccountManager */
    protected $paymentAccountManager;

    /** @var PayHeartland */
    protected $paymentManager;

    protected $reportLoader;

    /**
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
    public function createPaymentAccount(PaymentAccountData $paymentAccountData, User $user, Group $group)
    {
        return $this->paymentAccountManager->getToken($paymentAccountData, $user, $group);
    }

    /**
     * {@inheritdoc}
     */
    public function executeOrder(Order $order, PaymentAccount $paymentAccount, $paymentType = PaymentGroundType::RENT)
    {
        return $this->paymentManager->executePayment($order, $paymentAccount, $paymentType);
    }

    /**
     * {@inheritdoc}
     */
    public function loadReport()
    {
        return $this->reportLoader->loadReport();
    }
}
