<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Order;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\BillingAccountManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\EnrollmentManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\FundingAccountManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\LeastCostRouteManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\PaymentManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\ReportLoader;
use RentJeeves\CheckoutBundle\Payment\BusinessDaysCalculator;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as AccountData;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\PaymentGroundType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

/**
 * @DI\Service("payment_processor.aci_collect_pay")
 */
class PaymentProcessorAciCollectPay implements SubmerchantProcessorInterface
{
    const DELIVERY_BUSINESS_DAYS_FOR_BANK = 1;
    const DELIVERY_BUSINESS_DAYS_FOR_CARD = 1;

    /**
     * @var EnrollmentManager
     */
    protected $enrollmentManager;

    /**
     * @var BillingAccountManager
     */
    protected $billingAccountManager;

    /**
     * @var FundingAccountManager
     */
    protected $fundingAccountManager;

    /**
     * @var PaymentManager
     */
    protected $paymentManager;

    /**
     * @var LeastCostRouteManager
     */
    protected $leastCostRouteManager;

    /**
     * @var ReportLoader
     */
    protected $reportLoader;

    /**
     * @param EnrollmentManager $enrollmentManager
     * @param BillingAccountManager $billingAccountManager
     * @param FundingAccountManager $fundingAccountManager
     * @param PaymentManager $paymentManager
     * @param LeastCostRouteManager $leastCostRouteManager
     * @param ReportLoader $reportLoader
     *
     * @DI\InjectParams({
     *     "enrollmentManager" = @DI\Inject("payment_processor.aci.collect_pay.enrollment_manager"),
     *     "billingAccountManager" = @DI\Inject("payment_processor.aci.collect_pay.billing_account_manager"),
     *     "fundingAccountManager" = @DI\Inject("payment_processor.aci.collect_pay.funding_account_manager"),
     *     "paymentManager" = @DI\Inject("payment_processor.aci.collect_pay.payment_manager"),
     *     "leastCostRouteManager" = @DI\Inject("payment_processor.aci.collect_pay.lcr_manager"),
     *     "reportLoader" = @DI\Inject("payment_processor.aci.collect_pay.report_loader")
     * })
     */
    public function __construct(
        EnrollmentManager $enrollmentManager,
        BillingAccountManager $billingAccountManager,
        FundingAccountManager $fundingAccountManager,
        PaymentManager $paymentManager,
        LeastCostRouteManager $leastCostRouteManager,
        ReportLoader $reportLoader
    ) {
        $this->enrollmentManager = $enrollmentManager;
        $this->billingAccountManager = $billingAccountManager;
        $this->fundingAccountManager = $fundingAccountManager;
        $this->paymentManager = $paymentManager;
        $this->leastCostRouteManager = $leastCostRouteManager;
        $this->reportLoader = $reportLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ACIPayCollectV4';
    }

    /**
     * {@inheritdoc}
     */
    public function registerPaymentAccount(
        AccountData $accountData,
        DepositAccount $depositAccount
    ) {
        /** @var Tenant $tenant */
        $tenant = $accountData->getEntity()->getUser();
        $profile = $this->enrollmentManager->createUserProfile($tenant, $depositAccount);
        $this->billingAccountManager->addBillingAccount($profile, $depositAccount);
        $this->fundingAccountManager->addPaymentFundingAccount($profile, $accountData);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyPaymentAccount(
        AccountData $accountData
    ) {
        $this->fundingAccountManager->modifyPaymentFundingAccount($accountData);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function unregisterPaymentAccount(PaymentAccount $paymentAccount)
    {
        $this->fundingAccountManager->removePaymentFundingAccount($paymentAccount);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function registerBillingAccount(
        AccountData $accountData,
        Landlord $landlord
    ) {
        /** @var Group $group */
        $group = $accountData->getEntity()->getGroup();
        $profile = $this->enrollmentManager->createGroupProfile($group, $landlord);
        $this->fundingAccountManager->addBillingFundingAccount($profile, $accountData);

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
                PaymentProcessor::ACI
            );
        }

        if (PaymentGroundType::RENT === $paymentType) {
            $this->billingAccountManager->addBillingAccount(
                $order->getUser()->getAciCollectPayProfile(),
                $order->getDepositAccount()
            );
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
        return BusinessDaysCalculator::getBusinessDate($executeDate, 1);
    }

    /**
     * {@inheritdoc}
     */
    public function getCardType($cardNumber)
    {
        return $this->leastCostRouteManager->getLeastCostRoute($cardNumber);
    }

    /**
     * {@inheritdoc}
     */
    public function generateReversedBatchId(Order $order)
    {
        $today = new \DateTime();

        return sprintf('%sR%s', $order->getDepositAccount()->getId(), $today->format('Ymd'));
    }

    /**
     * @param  Order $order
     * @param  PaymentAccountInterface $paymentAccount
     * @return bool
     */
    protected function isAllowedToExecuteOrder(Order $order, PaymentAccountInterface $paymentAccount)
    {
        if ($order->getPaymentProcessor() == $paymentAccount->getPaymentProcessor() &&
            $order->getPaymentProcessor() == PaymentProcessor::ACI
        ) {
            return true;
        }

        return false;
    }
}
