<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland;

use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Payum2\Bundle\PayumBundle\Registry\ContainerAwareRegistry as PayumAwareRegistry;
use Payum2\Heartland\Model\PaymentDetails;
use Payum2\Request\BinaryMaskStatusRequest;
use Payum2\Request\CaptureRequest;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentAccountInterface;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

abstract class BasePayHeartland
{
    /** @var EntityManager */
    protected $em;

    /** @var object|\Payum2\PaymentInterface */
    protected $payum;

    /** @var PaymentDetailsMapper */
    protected $paymentDetailsMapper;

    /** @var string */
    protected $rtMerchantName;

    /**
     * @param EntityManager $em
     * @param PayumAwareRegistry $payum
     * @param PaymentDetailsMapper $paymentDetailsMapper
     * @param string $rtMerchantName
     */
    public function __construct(
        EntityManager $em,
        PayumAwareRegistry $payum,
        PaymentDetailsMapper $paymentDetailsMapper,
        $rtMerchantName
    ) {
        $this->em = $em;
        $this->payum = $payum->getPayment('heartland');
        $this->paymentDetailsMapper = $paymentDetailsMapper;
        $this->rtMerchantName = $rtMerchantName;
    }

    /**
     * Executes a payment taking money from given payment account.
     *
     * @param  Order          $order
     * @param  PaymentAccountInterface $paymentAccount
     * @param  string         $paymentType
     * @return string
     */
    public function executePayment(
        Order $order,
        PaymentAccountInterface $paymentAccount,
        $paymentType = PaymentGroundType::RENT
    ) {
        $paymentDetails = $this->getPaymentDetails($order, $paymentType);

        /** @var Transaction $transaction */
        $transaction = $this->paymentDetailsMapper->map($paymentDetails);
        $transaction->setOrder($order);

        if ($paymentAccount instanceof PaymentAccount) {
            $transaction->setPaymentAccount($paymentAccount);
        }

        $this->addToken($paymentDetails, $paymentAccount->getToken(), $order);

        $statusRequest = $this->execute($paymentDetails);

        $transaction = $this->paymentDetailsMapper->map($paymentDetails, $transaction);

        if (is_null($transaction->getAmount())) {
            $transaction->setAmount($order->getSum());
        }

        $isSuccessful = $statusRequest->isSuccess();
        $transaction->setIsSuccessful($isSuccessful);
        $order->addTransaction($transaction);

        $this->em->persist($transaction);

        return $this->getOrderStatus($order, $isSuccessful);
    }

    /**
     * @param  PaymentDetails          $paymentDetails
     * @return BinaryMaskStatusRequest
     */
    protected function execute(PaymentDetails $paymentDetails)
    {
        $captureRequest = new CaptureRequest($paymentDetails);
        $this->payum->execute($captureRequest);

        /** @var PaymentDetails $model */
        $model = $captureRequest->getModel();
        $statusRequest = new BinaryMaskStatusRequest($model);
        $this->payum->execute($statusRequest);

        return $statusRequest;
    }

    /**
     * @param Order $order
     * @param string $paymentType
     * @return PaymentDetails
     */
    abstract protected function getPaymentDetails(Order $order, $paymentType);

    /**
     * Defines orders status.
     * For credit card payments order already becomes COMPLETE, for ACH payments - PENDING.
     *
     * @param  Order  $order
     * @param  bool   $isSuccessful
     * @return string
     */
    abstract protected function getOrderStatus(Order $order, $isSuccessful);

    /**
     * @param PaymentDetails $paymentDetails
     * @param string $token
     * @param Order $order
     */
    abstract protected function addToken(PaymentDetails $paymentDetails, $token, Order $order);
}
