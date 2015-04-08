<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\PaymentProcessor\ACI\AciCollectPay\BillingAccountManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\ACI\AciCollectPay\EnrollmentManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\ACI\AciCollectPay\FundingAccountManager;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as PaymentAccountData;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\GroupAwareInterface;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

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
     * @param EnrollmentManager $enrollmentManager
     * @param BillingAccountManager $billingAccountManager
     * @param FundingAccountManager $fundingAccountManager
     *
     * @DI\InjectParams({
     *     "enrollmentManager" = @DI\Inject("payment.aci_collect_pay.enrollment_manager"),
     *     "billingAccountManager" = @DI\Inject("payment.aci_collect_pay.billing_account_manager"),
     *     "fundingAccountManager" = @DI\Inject( "payment.aci_collect_pay.funding_account_manager")
     * })
     */
    public function __construct(
        EnrollmentManager $enrollmentManager,
        BillingAccountManager $billingAccountManager,
        FundingAccountManager $fundingAccountManager
    ) {
        $this->enrollmentManager = $enrollmentManager;
        $this->billingAccountManager = $billingAccountManager;
        $this->fundingAccountManager = $fundingAccountManager;
    }

    /**
     * {@inheritdoc}
     */
    public function createPaymentAccount(PaymentAccountData $data, Contract $contract)
    {
        if ($data->getEntity() instanceof GroupAwareInterface) {
            throw new \Exception('Virtual Terminal is not implement yet for aci_collect_pay.');
        }

        $user = $contract->getTenant();

        if (!($profileId = $user->getAciCollectPayProfileId())) {
            $profileId = $this->enrollmentManager->createProfile($contract);
        } elseif ($contract->getAciCollectPayContractBilling()) {
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
    )
    {
        throw new \Exception('executeOrder is not implement yet for aci_collect_pay.');
    }

    /**
     * {@inheritdoc}
     */
    public function loadReport($reportType, array $settings = [])
    {
        throw new \Exception('loadReport is not implement yet for aci_collect_pay.');
    }
}