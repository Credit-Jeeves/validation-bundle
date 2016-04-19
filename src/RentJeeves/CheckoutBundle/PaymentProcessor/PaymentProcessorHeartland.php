<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\ChargeHeartland;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\PayHeartland;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\ReportLoader;
use RentJeeves\CheckoutBundle\Payment\BusinessDaysCalculator;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as AccountData;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\PaymentAccountManager;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\PaymentGroundType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

/**
 * @DI\Service("payment_processor.heartland")
 */
class PaymentProcessorHeartland implements SubmerchantProcessorInterface
{
    const DELIVERY_BUSINESS_DAYS_FOR_BANK = 3;
    const DELIVERY_BUSINESS_DAYS_FOR_CARD = 1;

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
    public function getName()
    {
        return 'HPS';
    }

    /**
     * {@inheritdoc}
     */
    public function registerPaymentAccount(
        AccountData $accountData,
        DepositAccount $depositAccount
    ) {
        $this->paymentAccountManager->registerPaymentToken($accountData, $depositAccount->getMerchantName());

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyPaymentAccount(
        AccountData $accountData
    ) {
        $this->paymentAccountManager->modifyPaymentAccount($accountData);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function unregisterPaymentAccount(PaymentAccount $paymentAccount)
    {
        $this->paymentAccountManager->removePaymentAccount($paymentAccount);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function registerBillingAccount(
        AccountData $accountData,
        Landlord $landlord
    ) {
        $this->paymentAccountManager->registerBillingToken($accountData, $landlord);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function executeOrder(
        Order $order,
        PaymentAccountInterface $accountEntity,
        $paymentType = PaymentGroundType::RENT
    ) {
        PaymentProcessorInvalidArgumentException::assertPaymentGroundType($paymentType);

        if (!$this->isAllowedToExecuteOrder($order, $accountEntity)) {
            throw PaymentProcessorInvalidArgumentException::invalidPaymentProcessor(
                PaymentProcessor::HEARTLAND
            );
        }

        if (PaymentGroundType::CHARGE === $paymentType) {
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
     * {@inheritdoc}
     */
    public function calculateDepositDate($paymentType, \DateTime $executeDate)
    {
        if ($paymentType === OrderPaymentType::BANK) {
            return BusinessDaysCalculator::getDepositDate($executeDate, self::DELIVERY_BUSINESS_DAYS_FOR_BANK);
        }

        return BusinessDaysCalculator::getDepositDate($executeDate, self::DELIVERY_BUSINESS_DAYS_FOR_CARD);
    }

    /**
     * {@inheritdoc}
     */
    public function getCardType($cardNumber)
    {
        return PaymentAccountType::CARD;
    }

    /**
     * {@inheritdoc}
     */
    public function generateReversedBatchId(Order $order)
    {
        // will be implemented later
        return null;
    }

    /**
     * @param  Order $order
     * @param  PaymentAccountInterface $paymentAccount
     * @return bool
     */
    protected function isAllowedToExecuteOrder(Order $order, PaymentAccountInterface $paymentAccount)
    {
        if ($order instanceof OrderSubmerchant &&
            $order->getPaymentProcessor() === PaymentProcessor::HEARTLAND &&
            $order->getPaymentProcessor() === $paymentAccount->getPaymentProcessor()
        ) {
            return true;
        }

        return false;
    }
}
