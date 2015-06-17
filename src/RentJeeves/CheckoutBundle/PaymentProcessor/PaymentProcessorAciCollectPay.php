<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Order;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\BillingAccountManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\EnrollmentManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\FundingAccountManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\PaymentManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\ReportLoader;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as AccountData;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\GroupAwareInterface;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\UserAwareInterface;
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
     * @var ReportLoader
     */
    protected $reportLoader;

    /**
     * @param EnrollmentManager $enrollmentManager
     * @param BillingAccountManager $billingAccountManager
     * @param FundingAccountManager $fundingAccountManager
     * @param PaymentManager $paymentManager
     * @param ReportLoader $reportLoader
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
        ReportLoader $reportLoader
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
    public function createPaymentToken(AccountData $data, Contract $contract)
    {
        if (!$data->getEntity() instanceof UserAwareInterface) {
            throw new PaymentProcessorInvalidArgumentException('Use createBillingToken for create Billing Account.');
        }

        if (!($profileId = $contract->getTenant()->getAciCollectPayProfileId())) {
            $profileId = $this->enrollmentManager->createUserProfile($contract);
        } elseif (!$contract->getAciCollectPayContractBilling()) {
            $this->billingAccountManager->addBillingAccount($profileId, $contract);
        }

        if ($fundingAccountId = $data->getEntity()->getToken()) {
            return $this
                ->fundingAccountManager
                ->modifyFundingAccount($fundingAccountId, $profileId, $data, $contract->getTenant());
        }

        return $this->fundingAccountManager->addFundingAccount($profileId, $data, $contract->getTenant());
    }

    /**
     * {@inheritdoc}
     */
    public function createBillingToken(AccountData $data, Landlord $landlord)
    {
        if (!$data->getEntity() instanceof GroupAwareInterface) {
            throw new PaymentProcessorInvalidArgumentException('Use createPaymentToken for create Payment Account.');
        }

        /** @var Group $group */
        $group = $data->getEntity()->getGroup();

        if (!($profileId = $group->getAciCollectPayProfileId())) {
            $profileId = $this->enrollmentManager->createGroupProfile($group, $landlord);
        }

        if ($fundingAccountId = $data->getEntity()->getToken()) {
            return $this
                ->fundingAccountManager
                ->modifyFundingAccount($fundingAccountId, $profileId, $data);
        }

        return $this->fundingAccountManager->addFundingAccount($profileId, $data);
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

        if ($paymentType === PaymentGroundType::RENT && !$this->isAllowedToExecuteOrder($order, $accountEntity)) {
            throw PaymentProcessorInvalidArgumentException::invalidPaymentProcessor(
                PaymentProcessor::ACI_COLLECT_PAY
            );
        }

        if (PaymentGroundType::CHARGE === $paymentType || PaymentGroundType::RENT === $paymentType) {
            return $this->paymentManager->executePayment($order, $accountEntity, $paymentType);
        } else {
            throw new \Exception(
                sprintf('executeOrder with paymentType = "%s" is not implement yet for aci_collect_pay.', $paymentType)
            );
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
     * @param  PaymentAccountInterface $paymentAccount
     * @return bool
     */
    protected function isAllowedToExecuteOrder(Order $order, PaymentAccountInterface $paymentAccount)
    {
        if ($paymentAccount instanceof PaymentAccount &&
            $order->getPaymentProcessor() == $paymentAccount->getPaymentProcessor() &&
            $order->getPaymentProcessor() == PaymentProcessor::ACI_COLLECT_PAY
        ) {
            return true;
        }

        return false;
    }
}
