<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentManagerInterface as PaymentManager;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\User;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as PaymentAccountData;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentAccountManagerInterface as PaymentAccountManager;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

/**
 * @DI\Service("payment_processor.heartland")
 */
class PaymentProcessorHeartland implements PaymentProcessorInterface
{
    /**
     * @var PaymentAccountManager
     */
    protected $paymentAccountManager;

    /**
     * @var PaymentManager
     */
    protected $paymentManager;

    /**
     * @DI\InjectParams({
     *     "paymentAccountManager" = @DI\Inject("payment.account.heartland"),
     *     "paymentManager" = @DI\Inject("payment.pay_heartland")
     * })
     */
    public function __construct(PaymentAccountManager $paymentAccountManager, PaymentManager $paymentManager)
    {
        $this->paymentAccountManager = $paymentAccountManager;
        $this->paymentManager = $paymentManager;
    }

    public function createPaymentAccount(PaymentAccountData $paymentAccountData, User $user, Group $group)
    {
        return $this->paymentAccountManager->getToken($paymentAccountData, $user, $group);
    }

    public function executePayment(Order $order, PaymentAccount $paymentAccount, $paymentType = PaymentGroundType::RENT)
    {
        return $this->paymentManager->executePayment($order, $paymentAccount, $paymentType);
    }

    public function processDepositReport(DateTime $date)
    {
        $this->isNotImplemented(__FUNCTION__);
    }

    public function processReversalReport(DateTime $date)
    {
        $this->isNotImplemented(__FUNCTION__);
    }

    public function isNotImplemented($functionName)
    {
        throw new \Exception("Function '$functionName' is not implemented yet");
    }
}
