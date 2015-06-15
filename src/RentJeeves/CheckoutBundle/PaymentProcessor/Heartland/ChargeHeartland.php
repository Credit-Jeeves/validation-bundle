<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland;

use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Payum2\Bundle\PayumBundle\Registry\ContainerAwareRegistry as PayumAwareRegistry;
use Payum2\Heartland\Soap\Base\BillTransaction;
use Payum2\Heartland\Soap\Base\CardProcessingMethod;
use Payum2\Heartland\Soap\Base\MakePaymentRequest;
use Payum2\Heartland\Soap\Base\TokenToCharge;
use Payum2\Request\BinaryMaskStatusRequest;
use Payum2\Request\CaptureRequest;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use Payum2\Heartland\Model\PaymentDetails;
use Payum2\Heartland\Soap\Base\Transaction as RequestTransaction;
use RentJeeves\DataBundle\Entity\BillingAccount;
use RentJeeves\DataBundle\Entity\Transaction;

/**
 * @DI\Service("payment.charge_heartland")
 */
class ChargeHeartland
{
    /** @var EntityManager */
    protected $em;

    /** @var object|\Payum2\PaymentInterface */
    protected $payment;

    /** @var PaymentDetailsMapper */
    protected $paymentDetailsMapper;

    /** @var string */
    protected $rtMerchantName;

    /**
     * @param EntityManager $em
     * @param PayumAwareRegistry $payum
     * @param PaymentDetailsMapper $paymentDetailsMapper
     * @param string $rtMerchantName
     *
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "payum" = @DI\Inject("payum2"),
     *     "paymentDetailsMapper" = @DI\Inject("payment.heartland.payment_details_mapper"),
     *     "rtMerchantName" = @DI\Inject("%rt_merchant_name%"),
     * })
     */
    public function __construct(
        EntityManager $em,
        PayumAwareRegistry $payum,
        PaymentDetailsMapper $paymentDetailsMapper,
        $rtMerchantName
    ) {
        $this->em = $em;
        $this->payment = $payum->getPayment('heartland');
        $this->paymentDetailsMapper = $paymentDetailsMapper;
        $this->rtMerchantName = $rtMerchantName;
    }

    /**
     * @param Order $order
     * @param BillingAccount $accountEntity
     * @return string
     */
    public function executePayment(Order $order, BillingAccount $accountEntity)
    {
        /** @var Operation $operation */
        if ((!$operation = $order->getOperations()->first()) || !($group = $operation->getGroup())) {
            throw new PaymentProcessorInvalidArgumentException();
        }

        $paymentRequest = new MakePaymentRequest();

        $billTransaction = new BillTransaction();
        $billTransaction->setID1(substr($group->getName(), 0, 50));
        $billTransaction->setID4($order->getDescriptor());
        $billTransaction->setBillType('Subscription Services');
        $billTransaction->setAmountToApplyToBill($order->getSum());
        $paymentRequest->getBillTransactions()->setBillTransaction([$billTransaction]);

        $tokenToCharge = new TokenToCharge();
        $tokenToCharge->setAmount($order->getSum());
        $tokenToCharge->setExpectedFeeAmount(0);
        $tokenToCharge->setCardProcessingMethod(CardProcessingMethod::UNASSIGNED);
        $tokenToCharge->setToken($accountEntity->getToken());

        $paymentRequest->getTokensToCharge()->setTokenToCharge([$tokenToCharge]);

        $requestTransaction = new RequestTransaction();
        $requestTransaction->setAmount($order->getSum());
        $requestTransaction->setFeeAmount(0);
        $paymentRequest->setTransaction($requestTransaction);

        $paymentDetails = new PaymentDetails();
        $paymentDetails->setMerchantName($this->rtMerchantName);
        $paymentDetails->setRequest($paymentRequest);

        $transaction = new Transaction();
        $transaction->setMerchantName($this->rtMerchantName);
        $transaction->setOrder($order);

        $captureRequest = new CaptureRequest($paymentDetails);
        $this->payment->execute($captureRequest);

        $statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
        $this->payment->execute($statusRequest);

        $transaction = $this->paymentDetailsMapper->map($paymentDetails, $transaction);

        $transaction->setAmount($order->getSum());
        $transaction->setIsSuccessful($statusRequest->isSuccess());
        $order->addTransaction($transaction);

        $this->em->persist($transaction);

        return $statusRequest->isSuccess() ? OrderStatus::COMPLETE : OrderStatus::ERROR;
    }
}
