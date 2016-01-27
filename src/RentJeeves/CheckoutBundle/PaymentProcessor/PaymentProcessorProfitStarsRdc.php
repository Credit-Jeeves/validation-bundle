<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorLogicException;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC\ReportLoader;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as AccountData;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

/**
 * Service name "payment_processor.profit_stars.rdc"
 */
class PaymentProcessorProfitStarsRdc implements SubmerchantProcessorInterface
{
    /** @var ReportLoader */
    protected $reportLoader;

    /**
     * @param ReportLoader $reportLoader
     */
    public function __construct(ReportLoader $reportLoader)
    {
        $this->reportLoader = $reportLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ProfitStarsRDC';
    }

    /**
     * {@inheritdoc}
     */
    public function registerPaymentAccount(
        AccountData $accountData,
        DepositAccount $depositAccount
    ) {
        throw new PaymentProcessorLogicException('registerPaymentAccount is not implemented for ProfitStars');
    }

    /**
     * {@inheritdoc}
     */
    public function modifyPaymentAccount(
        AccountData $accountData
    ) {
        throw new PaymentProcessorLogicException('modifyPaymentAccount is not implemented for ProfitStars');
    }

    /**
     * {@inheritdoc}
     */
    public function unregisterPaymentAccount(PaymentAccount $paymentAccount)
    {
        throw new PaymentProcessorLogicException('unregisterPaymentAccount is not implemented for ProfitStars');
    }

    /**
     * {@inheritdoc}
     */
    public function registerBillingAccount(
        AccountData $accountData,
        Landlord $landlord
    ) {
        throw new PaymentProcessorLogicException('registerBillingAccount is not implemented for ProfitStars');
    }

    /**
     * {@inheritdoc}
     */
    public function executeOrder(
        Order $order,
        PaymentAccountInterface $accountEntity,
        $paymentType = PaymentGroundType::RENT
    ) {
        throw new PaymentProcessorLogicException('executeOrder is not implemented for ProfitStars');
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
        throw new PaymentProcessorLogicException('calculateDepositDate is not implemented for ProfitStars');
    }

    /**
     * {@inheritdoc}
     */
    public function generateReversedBatchId(Order $order)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCardType($cardNumber)
    {
        throw new PaymentProcessorLogicException('getCardType is not implemented for ProfitStars');
    }
}
