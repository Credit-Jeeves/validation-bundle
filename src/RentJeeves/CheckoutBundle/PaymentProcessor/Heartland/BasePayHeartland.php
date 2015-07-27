<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland;

use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Payum2\Bundle\PayumBundle\Registry\ContainerAwareRegistry as PayumAwareRegistry;
use Payum2\Heartland\Model\PaymentDetails;
use Payum2\Request\BinaryMaskStatusRequest;
use Payum2\Request\CaptureRequest;
use RentJeeves\CheckoutBundle\Payment\BusinessDaysCalculator;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
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
     * @param  OrderSubmerchant          $order
     * @param  PaymentAccountInterface $paymentAccount
     * @param  string         $paymentType
     * @return bool
     */
    public function executePayment(
        OrderSubmerchant $order,
        PaymentAccountInterface $paymentAccount,
        $paymentType = PaymentGroundType::RENT
    ) {
        PaymentProcessorInvalidArgumentException::assertPaymentGroundType($paymentType);

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

        if ($isSuccessful && OrderPaymentType::CARD === $order->getPaymentType()) {
            $batchDate = clone $transaction->getCreatedAt();
            $transaction->setBatchDate($batchDate);
            $transaction->setDepositDate(BusinessDaysCalculator::getNextBusinessDate(clone $batchDate));
        }

        return !!$isSuccessful;
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
     * @param OrderSubmerchant $order
     * @param string $paymentType
     * @return PaymentDetails
     */
    abstract protected function getPaymentDetails(OrderSubmerchant $order, $paymentType);

    /**
     * @param PaymentDetails $paymentDetails
     * @param string $token
     * @param OrderSubmerchant $order
     */
    abstract protected function addToken(PaymentDetails $paymentDetails, $token, OrderSubmerchant $order);
}
