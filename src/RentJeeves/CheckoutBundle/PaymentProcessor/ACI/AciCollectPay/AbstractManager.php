<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\ACI\AciCollectPay;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Monolog\Logger;
use Payum\AciCollectPay\Model\SubModel\Address;
use Payum\AciCollectPay\Model\SubModel\BillingAccount;
use Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry as PayumAwareRegistry;
use Payum\Core\Payment as PaymentProcessor;
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
     * @var Logger
     */
    protected $logger;

    /**
     * @param EntityManager $em
     * @param PayumAwareRegistry $payum
     * @param Logger $logger
     *
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "payum" = @DI\Inject("payum"),
     *     "logger" = @DI\Inject("logger"),
     * })
     */
    public function __construct(EntityManager $em, PayumAwareRegistry $payum, Logger $logger)
    {
        $this->em = $em;

        $this->paymentProcessor = $payum->getPayment('aci_collect_pay');

        $this->logger = $logger;
    }

    /**
     * @param Contract $contract
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

        $billingAccountAddress->setAddress1($contract->getProperty()->getAddress());
        $billingAccountAddress->setAddress2($contract->getUnit()->getName());
        $billingAccountAddress->setCity($contract->getProperty()->getCity());
        $billingAccountAddress->setPostalCode($contract->getProperty()->getZip());
        $billingAccountAddress->setState($contract->getProperty()->getArea());
        $billingAccountAddress->setCountryCode($contract->getProperty()->getCountry());

        $billingAccount->setAddress($billingAccountAddress);

        return $billingAccount;
    }
}
