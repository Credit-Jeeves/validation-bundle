<?php

namespace RentJeeves\CheckoutBundle\Payment;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Enum\OperationType;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use RentJeeves\DataBundle\Entity\Heartland as PaymentDetails;
use Payum\Heartland\Soap\Base\BillTransaction;
use Payum\Heartland\Soap\Base\CardProcessingMethod;
use Payum\Heartland\Soap\Base\MakePaymentRequest;
use Payum\Heartland\Soap\Base\TokenToCharge;
use Payum\Heartland\Soap\Base\Transaction;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
use RentJeeves\DataBundle\Entity\Landlord;
use RuntimeException;

/**
 * @DI\Service("payment_terminal")
 */
class Terminal
{
    protected $em;
    protected $payment;
    protected $merchantName;
    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "payum" = @DI\Inject("payum"),
     *     "merchantName" = @DI\Inject("%rt_merchant_name%"),
     * })
     */
    public function __construct($em, $payum, $merchantName)
    {
        $this->em = $em;
        $this->payment = $payum->getPayment('heartland');
        $this->merchantName = $merchantName;
    }

    public function pay(Group $group, $amount, $id4)
    {
        $order = new Order();
        $operation = new Operation();
        $operation->setType(OperationType::CHARGE);
        $operation->setAmount($amount);
        $operation->setGroup($group);
        $order->addOperation($operation);

        $users = $group->getGroupAgents();
        $groupUser = $users->first();
        if (!$groupUser) {
            throw new RuntimeException("Group user not found");
        }

        $order->setType(OrderType::HEARTLAND_BANK);
        $order->setUser($groupUser);
        $order->setAmount($amount);
        $order->setStatus(OrderStatus::NEWONE);

        $paymentRequest = new MakePaymentRequest();

        $billTransaction = new BillTransaction();
        $billTransaction->setID1($group->getName());
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

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setFeeAmount(0);
        $paymentRequest->setTransaction($transaction);

        $paymentDetails = new PaymentDetails();
        $paymentDetails->setMerchantName($this->merchantName);
        $paymentDetails->setRequest($paymentRequest);
        $paymentDetails->setOrder($order);

        $this->em->persist($order);
        $this->em->flush();

        $captureRequest = new CaptureRequest($paymentDetails);
        $this->payment->execute($captureRequest);

        $statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
        $this->payment->execute($statusRequest);
        $order->addHeartland($paymentDetails);
        if ($statusRequest->isSuccess()) {
            $order->setStatus(OrderStatus::COMPLETE);
        } else {
            $order->setStatus(OrderStatus::ERROR);
        }

        $paymentDetails->setAmount($amount);
        $paymentDetails->setIsSuccessful($statusRequest->isSuccess());
        $this->em->persist($paymentDetails);
        $this->em->flush();

        return $statusRequest->getModel();
    }
} 
