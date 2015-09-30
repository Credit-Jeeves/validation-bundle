<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay;

use Doctrine\ORM\EntityManager;
use Payum\AciCollectPay\Model\SubModel\Address;
use Payum\AciCollectPay\Model\SubModel\BillingAccount;
use Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry as PayumAwareRegistry;
use Payum\Core\Payment as PaymentProcessor;
use RentJeeves\DataBundle\Entity\AciCollectPayUserProfile;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\PaymentProcessor as PaymentProcessorEnum;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\DepositAccountType;

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
     * @link https://credit.atlassian.net/browse/RT-1483
     *
     * @param $message
     * @return string
     */
    public static function removeDebugInformation($message)
    {
        if (empty($message) || !is_string($message)) {
            return $message;
        }
        $messages = explode(':', $message);

        if (count($messages) === 1) {
            return $message;
        }
        //We always need to remove the first element of message
        unset($messages[0]);
        $messages[1] = trim($messages[1]);
        //if second element is numeric we should remove it too
        if (is_numeric($messages[1]) && count($messages) > 1) {
            unset($messages[1]);
        }

        return trim(implode(':', $messages));
    }

    /**
     * @param  Contract $contract
     * @param  string $depositAccountType
     * @return BillingAccount
     */
    protected function prepareBillingAccount(Tenant $user, DepositAccount $depositAccount)
    {
        $billingAccount = new BillingAccount();

        $group = $depositAccount->getGroup();

        $billingAccount->setAccountNumber($depositAccount->getMerchantName()); // ????
        $billingAccount->setBusinessId($depositAccount->getMerchantName());
        $billingAccount->setHoldername($user->getFullName());
        $billingAccount->setNickname($group->getName() . $depositAccount->getMerchantName()); // ????

        $billingAccountAddress = new Address();

        $billingAccountAddress->setAddress1((string) $group->getStreetAddress1());
        $billingAccountAddress->setAddress2((string) $group->getStreetAddress2());
        $billingAccountAddress->setCity((string) $group->getCity());
        $billingAccountAddress->setPostalCode((string) $group->getZip());
        $billingAccountAddress->setState((string) $group->getState());
        $billingAccountAddress->setCountryCode($group->getCountry());

        $billingAccount->setAddress($billingAccountAddress);

        return $billingAccount;
    }
}
