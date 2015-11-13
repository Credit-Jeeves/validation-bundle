<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Payum\AciCollectPay\Model\SubModel\Address;
use Payum\AciCollectPay\Model\SubModel\BillingAccount;
use Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry as PayumAwareRegistry;
use Payum\Core\Payment as PaymentProcessor;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Tenant;
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
    protected $virtualTerminalBusinessId;

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
    public function setVirtualTerminalBusinessId($businessId)
    {
        $this->virtualTerminalBusinessId = $businessId;
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
        $billingAccount->setAccountNumber(
            $this->getUserBillingAccountNumber($user, $depositAccount->getMerchantName())
        );
        $billingAccount->setBusinessId($depositAccount->getMerchantName());
        $billingAccount->setHoldername($user->getFullName());
        $billingAccount->setNickname($this->getBillingAccountNickname($depositAccount));

        $billingAccountAddress = $this->getBillingAccountAddress($depositAccount);
        $billingAccount->setAddress($billingAccountAddress);

        return $billingAccount;
    }

    /**
     * @param User $user
     * @param string $divisionId
     * @return string
     */
    protected static function getUserBillingAccountNumber(User $user, $divisionId)
    {
        return sprintf('%s%s', $divisionId, $user->getId());
    }

    /**
     * @param Group $group
     * @param string $divisionId
     * @return string
     */
    protected static function getGroupBillingAccountNumber(Group $group, $divisionId)
    {
        return sprintf('%s%s', $divisionId, $group->getId());
    }

    /**
     * @param DepositAccount $depositAccount
     * @return string
     */
    protected static function getBillingAccountNickname(DepositAccount $depositAccount)
    {
        return sprintf('%s-%s', $depositAccount->getGroup()->getName(), $depositAccount->getMerchantName());
    }

    /**
     * @param DepositAccount $depositAccount
     * @return Address
     */
    protected function getBillingAccountAddress(DepositAccount $depositAccount)
    {
        $billingAccountAddress = new Address();
        $group = $depositAccount->getGroup();

        if ($group->getStreetAddress1() && $group->getCity() && $group->getZip() && $group->getState()) {
            $billingAccountAddress->setAddress1($group->getStreetAddress1());
            $billingAccountAddress->setAddress2($group->getStreetAddress2());
            $billingAccountAddress->setCity($group->getCity());
            $billingAccountAddress->setPostalCode($group->getZip());
            $billingAccountAddress->setState($group->getState());
            $billingAccountAddress->setCountryCode($group->getCountry());
        } else {
            $billingAccountAddress->setAddress1('');
            $billingAccountAddress->setAddress2('');
            $billingAccountAddress->setCity('');
            $billingAccountAddress->setPostalCode('');
            $billingAccountAddress->setState('');
            $billingAccountAddress->setCountryCode($group->getCountry());
        }

        return $billingAccountAddress;
    }
}
