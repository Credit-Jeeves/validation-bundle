<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay;

use Doctrine\ORM\EntityManager;
use Payum\AciCollectPay\Model\SubModel\Address;
use Payum\AciCollectPay\Model\SubModel\BillingAccount;
use Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry as PayumAwareRegistry;
use Payum\Core\Payment as PaymentProcessor;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Contract;

abstract class AbstractManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PaymentProcessor
     */
    protected $paymentProcessor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $defaultBusinessId;

    /**
     * @param EntityManager      $em
     * @param PayumAwareRegistry $payum
     * @param LoggerInterface    $logger
     */
    public function __construct(EntityManager $em, PayumAwareRegistry $payum, LoggerInterface $logger)
    {
        $this->em = $em;

        $this->paymentProcessor = $payum->getPayment('aci_collect_pay');

        $this->logger = $logger;
    }

    /**
     * @param string $businessId
     */
    public function setDefaultBusinessId($businessId)
    {
        $this->defaultBusinessId = $businessId;
    }

    /**
     * @param  Contract       $contract
     * @return BillingAccount
     */
    protected function prepareBillingAccount(Contract $contract)
    {
        $billingAccount = new BillingAccount();

        $group = $contract->getGroup();

        $billingAccount->setAccountNumber($contract->getId());
        $billingAccount->setBusinessId($group->getAciCollectPaySettings()->getBusinessId());
        $billingAccount->setHoldername($contract->getTenant()->getFullName());
        $billingAccount->setNickname($group->getName() . $contract->getId());

        $billingAccountAddress = new Address();

        $billingAccountAddress->setAddress1((string) $contract->getProperty()->getAddress());
        $billingAccountAddress->setAddress2((string) $contract->getUnit()->getName());
        $billingAccountAddress->setCity((string) $contract->getProperty()->getCity());
        $billingAccountAddress->setPostalCode((string) $contract->getProperty()->getZip());
        $billingAccountAddress->setState((string) $contract->getProperty()->getArea());
        $billingAccountAddress->setCountryCode($contract->getProperty()->getCountry());

        $billingAccount->setAddress($billingAccountAddress);

        return $billingAccount;
    }
}
