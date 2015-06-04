<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\PaymentProcessor\ACI\AciCollectPay\BillingAccountManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\ACI\AciCollectPay\EnrollmentManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\ACI\AciCollectPay\FundingAccountManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\ACI\AciCollectPay\PaymentManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as PaymentAccountData;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\GroupAwareInterface;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentGroundType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

/**
 * @DI\Service("payment_processor.aci_collect_pay")
 */
class PaymentProcessorAciCollectPay implements PaymentProcessorInterface
{
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
     * @var ReportLoaderInterface
     */
    protected $reportLoader;

    /**
     * @param EnrollmentManager $enrollmentManager
     * @param BillingAccountManager $billingAccountManager
     * @param FundingAccountManager $fundingAccountManager
     * @param PaymentManager $paymentManager
     * @param ReportLoaderInterface $reportLoader
     *
     * @DI\InjectParams({
     *     "enrollmentManager" = @DI\Inject("payment_processor.aci.collect_pay.enrollment_manager"),
     *     "billingAccountManager" = @DI\Inject("payment_processor.aci.collect_pay.billing_account_manager"),
     *     "fundingAccountManager" = @DI\Inject("payment_processor.aci.collect_pay.funding_account_manager"),
     *     "paymentManager" = @DI\Inject("payment_processor.aci.collect_pay.payment_manager"),
     *     "reportLoader" = @DI\Inject("payment_processor.aci.collect_pay.report_loader")
     * })
     */
    public function __construct(
        EnrollmentManager $enrollmentManager,
        BillingAccountManager $billingAccountManager,
        FundingAccountManager $fundingAccountManager,
        PaymentManager $paymentManager,
        ReportLoaderInterface $reportLoader
    ) {
        $this->enrollmentManager = $enrollmentManager;
        $this->billingAccountManager = $billingAccountManager;
        $this->fundingAccountManager = $fundingAccountManager;
        $this->paymentManager = $paymentManager;
        $this->reportLoader = $reportLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function createPaymentAccount(PaymentAccountData $data, Contract $contract)
    {
        if ($data->getEntity() instanceof GroupAwareInterface) {
            throw new \Exception('Virtual Terminal is not implement yet for aci_collect_pay.');
        }

        if (!($profileId = $contract->getTenant()->getAciCollectPayProfileId())) {
            $profileId = $this->enrollmentManager->createProfile($contract);
        } elseif (!$contract->getAciCollectPayContractBilling()) {
            $this->billingAccountManager->addBillingAccount($profileId, $contract);
        }

        if ($fundingAccountId = $data->getEntity()->getToken()) {
            return $this
                ->fundingAccountManager
                ->modifyFundingAccount($fundingAccountId, $profileId, $data, $contract);
        }

        return $this->fundingAccountManager->addFundingAccount($profileId, $data, $contract);
    }

    /**
     * {@inheritdoc}
     */
    public function executeOrder(
        Order $order,
        PaymentAccount $paymentAccount,
        $paymentType = PaymentGroundType::RENT
    ) {
        if (!$this->isAllowedToExecuteOrder($order, $paymentAccount)) {
            throw PaymentProcessorInvalidArgumentException::invalidPaymentProcessor(
                PaymentProcessor::ACI_COLLECT_PAY
            );
        }

        if (PaymentGroundType::RENT == $paymentType) {
            return $this->paymentManager->executePayment($order, $paymentAccount);
        } else {
            throw new \Exception('executeOrder with paymentType = "report" is not implement yet for aci_collect_pay.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadReport()
    {
        return $this->reportLoader->loadReport();
    }

    /**
     * @param  Order $order
     * @param  PaymentAccount $paymentAccount
     * @return bool
     */
    protected function isAllowedToExecuteOrder(Order $order, PaymentAccount $paymentAccount)
    {
        if ($order->getPaymentProcessor() == $paymentAccount->getPaymentProcessor() &&
            $order->getPaymentProcessor() == PaymentProcessor::ACI_COLLECT_PAY
        ) {
            return true;
        }

        return false;
    }
}
