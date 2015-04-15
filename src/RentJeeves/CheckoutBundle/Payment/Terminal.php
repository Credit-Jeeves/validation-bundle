<?php

namespace RentJeeves\CheckoutBundle\Payment;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Enum\OperationType;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use Payum2\Heartland\Model\PaymentDetails;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\PaymentDetailsMapper;
use RentJeeves\DataBundle\Entity\Transaction;
use Payum2\Heartland\Soap\Base\BillTransaction;
use Payum2\Heartland\Soap\Base\CardProcessingMethod;
use Payum2\Heartland\Soap\Base\MakePaymentRequest;
use Payum2\Heartland\Soap\Base\TokenToCharge;
use Payum2\Heartland\Soap\Base\Transaction as RequestTransaction;
use Payum2\Request\BinaryMaskStatusRequest;
use Payum2\Request\CaptureRequest;
use Payum2\Bundle\PayumBundle\Registry\ContainerAwareRegistry as PayumAwareRegistry;
use Payum2\Payment as PaymentProcessor;
use RuntimeException;
use DateTime;

/**
 * @DI\Service("payment_terminal")
 */
class Terminal
{
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var PaymentProcessor
     */
    protected $payment;
    /**
     * @var PaymentDetailsMapper
     */
    protected $paymentDetailsMapper;
    /**
     * @var string
     */
    protected $merchantName;

    /**
     * @param EntityManager $em
     * @param PayumAwareRegistry $payum
     * @param PaymentDetailsMapper $paymentDetailsMapper
     * @param string $merchantName
     *
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "payum" = @DI\Inject("payum2"),
     *     "paymentDetailsMapper" = @DI\Inject("payment.heartland.payment_details_mapper"),
     *     "merchantName" = @DI\Inject("%rt_merchant_name%"),
     * })
     */
    public function __construct(
        EntityManager $em,
        PayumAwareRegistry $payum,
        PaymentDetailsMapper $paymentDetailsMapper,
        $merchantName
    ) {
        $this->em = $em;
        $this->payment = $payum->getPayment('heartland');
        $this->paymentDetailsMapper = $paymentDetailsMapper;
        $this->merchantName = $merchantName;
    }

    public function pay(Group $group, $amount, $id4)
    {
        $order = new Order();
        $operation = new Operation();
        $operation->setType(OperationType::CHARGE);
        $operation->setAmount($amount);
        $operation->setGroup($group);
        $operation->setPaidFor(new DateTime());
        $order->addOperation($operation);

        $users = $group->getGroupAgents();
        if ($users->count() == 0) {
            throw new RuntimeException("Group user not found");
        }

        $groupUser = $users->first();

        $order->setType(OrderType::HEARTLAND_BANK);
        $order->setUser($groupUser);
        $order->setSum($amount);
        $order->setStatus(OrderStatus::NEWONE);

        $paymentRequest = new MakePaymentRequest();

        $billTransaction = new BillTransaction();
        $billTransaction->setID1(substr($group->getName(), 0, 50));
        $billTransaction->setID4($id4);
        $billTransaction->setBillType('Subscription Services');
        $billTransaction->setAmountToApplyToBill($amount);
        $paymentRequest->getBillTransactions()->setBillTransaction(array($billTransaction));

        $tokenToCharge = new TokenToCharge();
        $tokenToCharge->setAmount($amount);
        $tokenToCharge->setExpectedFeeAmount(0);
        $tokenToCharge->setCardProcessingMethod(CardProcessingMethod::UNASSIGNED);
        $tokenToCharge->setToken($group->getActiveBillingAccount()->getToken());

        $paymentRequest->getTokensToCharge()->setTokenToCharge(array($tokenToCharge));

        $requestTransaction = new RequestTransaction();
        $requestTransaction->setAmount($amount);
        $requestTransaction->setFeeAmount(0);
        $paymentRequest->setTransaction($requestTransaction);

        $paymentDetails = new PaymentDetails();
        $paymentDetails->setMerchantName($this->merchantName);
        $paymentDetails->setRequest($paymentRequest);

        $transaction = new Transaction();
        $transaction->setMerchantName($this->merchantName);
        $transaction->setOrder($order);
        $this->em->persist($order);
        $this->em->flush();

        $captureRequest = new CaptureRequest($paymentDetails);
        $this->payment->execute($captureRequest);

        $statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
        $this->payment->execute($statusRequest);

        $transaction = $this->paymentDetailsMapper->map($paymentDetails, $transaction);
        $order->addTransaction($transaction);

        if ($statusRequest->isSuccess()) {
            $order->setStatus(OrderStatus::COMPLETE);
        } else {
            $order->setStatus(OrderStatus::ERROR);
        }

        $transaction->setAmount($amount);
        $transaction->setIsSuccessful($statusRequest->isSuccess());
        $this->em->persist($transaction);
        $this->em->flush();

        return $transaction;
    }
}
