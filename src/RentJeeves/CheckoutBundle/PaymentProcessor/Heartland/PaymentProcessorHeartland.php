<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland;

use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorInterface;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\User;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as PaymentAccountData;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Payment;
use Symfony\Component\DependencyInjection\Container;

/**
 * @DI\Service("payment_processor.heartland")
 */
class PaymentProcessorHeartland implements PaymentProcessorInterface
{
    protected $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function createDepositAccount(Group $group)
    {
        $this->isNotImplemented(__FUNCTION__);
    }

    public function createPaymentAccount(PaymentAccountData $paymentAccountData, User $user, Group $group)
    {
        $token = $this->container->get('payment.account.heartland')->getToken($paymentAccountData, $user, $group);

        return $token;
    }

    public function executePayment(Payment $payment)
    {
        $this->isNotImplemented(__FUNCTION__);
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
