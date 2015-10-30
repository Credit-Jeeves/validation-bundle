<?php

namespace RentJeeves\CoreBundle\PaymentProcessorMigration\Mapper;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityRepository;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\BillingAccountManager;
use RentJeeves\ComponentBundle\Utility\ShorteningAddressUtility;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Exception\CsvMapException;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\AccountRecord;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\ConsumerRecord;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\FundingRecord;
use RentJeeves\DataBundle\Entity\AciImportProfileMap;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\MerchantAccountMigration;
use RentJeeves\DataBundle\Entity\PaymentAccountHpsMerchant;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

class AciProfileMapper
{
    /**
     * @var AciImportProfileMap
     */
    protected $profile;

    /**
     * @var Holding[]
     */
    protected $holdings;

    /**
     * @var string
     */
    protected $businessId;

    /**
     * @var EntityRepository
     */
    protected $merchantAccountRepo;

    /**
     * @param string $businessId
     * @param EntityRepository $repository
     */
    public function __construct($businessId, EntityRepository $repository)
    {
        $this->businessId = $businessId;
        $this->merchantAccountRepo = $repository;
    }

    /**
     * Map Group or User into multiple models
     * and
     * return these models
     *
     * @param AciImportProfileMap $profileMap
     * @param array $holdings
     *
     * @return array
     */
    public function map(AciImportProfileMap $profileMap, array $holdings = null)
    {
        $this->profile = $profileMap;
        $this->holdings = $holdings;

        if (null !== $this->profile->getUser()) {
            $records = $this->mapUser();
        } else {
            $records = $this->mapGroup();
        }
        /**
         * Return all not empty values
         */

        return array_values(array_filter($records));
    }

    /**
     * @return array
     */
    protected function mapUser()
    {
        try {
            return array_merge(
                [$this->mapUserConsumerRecord()],
                $this->mapUserAccountRecords(),
                $this->mapUserFundingRecords()
            );
        } catch (CsvMapException $e) {
            return [];
        }
    }

    /**
     * @return null|ConsumerRecord
     *
     * @throws CsvMapException if we can`t get address for User
     */
    protected function mapUserConsumerRecord()
    {
        $user = $this->profile->getUser();
        if (null !== $user->getAciCollectPayProfile()) {
            return null;
        }

        $consumerRecord = new ConsumerRecord();
        $consumerRecord->setProfileId($this->profile->getId());
        $consumerRecord->setBusinessId($this->businessId);
        $consumerRecord->setUserName(substr($user->getUsername(), 0, 32));
        $consumerRecord->setPassword(substr($user->getUsername(), 0, 32)); // Any value
        $consumerRecord->setConsumerFirstName($user->getFirstName());
        $consumerRecord->setConsumerLastName($user->getLastName());
        $consumerRecord->setPrimaryEmailAddress($user->getEmail());

        if (null !== $address = $user->getDefaultAddress()) {
            $consumerRecord->setAddress1(ShorteningAddressUtility::shrinkAddress((string) $address, 64));
            $consumerRecord->setCity(substr($address->getCity(), 0, 12));
            $consumerRecord->setState($address->getArea());
            $consumerRecord->setZipCode($address->getZip());
        } else {
            if (null === $contract = $this->getContractForUser($user)) {
                throw new CsvMapException();
            }
            $property = $contract->getProperty();
            $propertyAddress = $property->getPropertyAddress();

            $consumerRecord->setAddress1(ShorteningAddressUtility::shrinkAddress($propertyAddress->getAddress(), 64));
            $consumerRecord->setCity(substr($propertyAddress->getCity(), 0, 12));
            $consumerRecord->setState($propertyAddress->getState());
            $consumerRecord->setZipCode($propertyAddress->getZip());
        }

        return $consumerRecord;
    }

    /**
     * Return Contract or NULL if tenant doesn't have contracts
     *
     * @param Tenant $user
     * @return \RentJeeves\DataBundle\Entity\Contract|null
     */
    protected function getContractForUser(Tenant $user)
    {
        $activeContracts = $user->getActiveContracts();
        if (false === empty($activeContracts)) {
            return $activeContracts[0];
        }

        $allContracts = $user->getContracts();
        if (false === $allContracts->isEmpty()) {
            return $allContracts->first();
        }

        return null;
    }

    /**
     * @return AccountRecord[]
     */
    protected function mapUserAccountRecords()
    {
        $user = $this->profile->getUser();
        $address = $user->getDefaultAddress();
        $records = [];

        /** @var PaymentAccountHpsMerchant $merchant */
        foreach ($this->getFilteredMerchantsForUser($user) as $merchant) {
            /** @var MerchantAccountMigration $merchantAccountMigration */
            $merchantAccountMigration = $this->merchantAccountRepo->findOneBy(
                ['heartlandMerchantName' => $merchant->getMerchantName()]
            );
            /* if there is no HPS-to-ACI merchant account
             * or user already has profile with enrolled billing account for given division id,
             * then do nothing.
             */
            if (null === $merchantAccountMigration || (null !== $profile = $user->getAciCollectPayProfile() and
                $profile->hasBillingAccountForDivisionId($merchantAccountMigration->getAciDivisionId()))
            ) {
                continue;
            }

            $accountRecord = new AccountRecord();
            $accountRecord->setProfileId($this->profile->getId());
            $accountRecord->setBillingAccountNumber(BillingAccountManager::createUserBillingAccountNumber(
                $user,
                $merchantAccountMigration->getAciDivisionId()
            ));
            $accountRecord->setDivisionId($merchantAccountMigration->getAciDivisionId());
            $accountRecord->setNameOnBillingAccount($user->getFirstName() . ' ' . $user->getLastName());
            $accountRecord->setAddress1((string) $address);
            $accountRecord->setCity($address ? substr($address->getCity(), 0, 12) : '');
            $accountRecord->setState($address ? $address->getArea() : '');
            $accountRecord->setZipCode($address ? $address->getZip() : '');
            $accountRecord->setBusinessId($this->businessId);

            $records[] = $accountRecord;
        }

        return $records;
    }

    /**
     * @return AccountRecord[]
     */
    protected function mapUserFundingRecords()
    {
        $user = $this->profile->getUser();
        $records = [];
        foreach ($user->getPaymentAccounts() as $paymentAccount) {
            /**
             * if we have at least 1 ACI paymentAccount ->
             * we already enrolled all funding accounts for this user.
             * So we don`t need to enroll any payment accounts
             */
            if ($paymentAccount->getPaymentProcessor() === PaymentProcessor::ACI) {
                return [];
            }
            $fundingRecord = new FundingRecord();
            $fundingRecord->setProfileId($this->profile->getId());
            $fundingRecord->setFundingAccountHolderAddress2($paymentAccount->getToken());
            $fundingRecord->setBusinessId($this->businessId);

            $records[] = $fundingRecord;
        }

        return $records;
    }

    /**
     * @return array
     */
    protected function mapGroup()
    {
        return array_merge(
            [$this->mapGroupConsumerRecord()],
            [$this->mapGroupAccountRecord()],
            $this->mapGroupFundingRecords()
        );
    }

    /**
     * @return ConsumerRecord|null
     */
    protected function mapGroupConsumerRecord()
    {
        $group = $this->profile->getGroup();
        if (null !== $group->getAciCollectPayProfile()) {
            return null;
        }
        /** @var Landlord $landlord */
        if (false == $landlord = $group->getGroupAgents()->first()) {
            return null;
        }

        $address = $landlord->getDefaultAddress();

        $consumerRecord = new ConsumerRecord();
        $consumerRecord->setProfileId($this->profile->getId());
        $consumerRecord->setBusinessId($this->businessId);
        $consumerRecord->setUserName(md5('G' . $group->getId()));
        $consumerRecord->setPassword(md5('G' . $group->getId())); // Any value
        $consumerRecord->setConsumerFirstName($landlord->getFirstName());
        $consumerRecord->setConsumerLastName($landlord->getLastName());
        $consumerRecord->setPrimaryEmailAddress($landlord->getEmail());
        $consumerRecord->setAddress1((string) $address);
        $consumerRecord->setCity($address ? substr($address->getCity(), 0, 12) : '');
        $consumerRecord->setState($address ? $address->getArea() : '');
        $consumerRecord->setZipCode($address ? $address->getZip() : '');

        return $consumerRecord;
    }

    /**
     * @return AccountRecord|null
     */
    protected function mapGroupAccountRecord()
    {
        $group = $this->profile->getGroup();
        if (($this->holdings !== null && false === in_array($group->getHolding(), $this->holdings)) ||
            null !== $group->getAciCollectPayProfile()
        ) {
            return null;
        }

        $accountRecord = new AccountRecord();
        $accountRecord->setProfileId($this->profile->getId());
        $accountRecord->setBillingAccountNumber(
            BillingAccountManager::createGroupBillingAccountNumber($group, $this->businessId)
        );
        $accountRecord->setDivisionId($this->businessId);
        $accountRecord->setNameOnBillingAccount($group->getName());
        $accountRecord->setAddress1($group->getStreetAddress1());
        $accountRecord->setCity($group->getCity());
        $accountRecord->setState($group->getState());
        $accountRecord->setZipCode($group->getZip());
        $accountRecord->setBusinessId($this->businessId);

        return $accountRecord;
    }

    /**
     * @return FundingRecord[]
     */
    protected function mapGroupFundingRecords()
    {
        $group = $this->profile->getGroup();
        $records = [];
        foreach ($group->getBillingAccounts() as $billingAccount) {
            /**
             * if we have at least 1 ACI billingAccount ->
             * we already enrolled all funding accounts for this group.
             * So we don`t need to enroll any  billing accounts
             */
            if ($billingAccount->getPaymentProcessor() === PaymentProcessor::ACI) {
                return [];
            }
            $fundingRecord = new FundingRecord();
            $fundingRecord->setProfileId($this->profile->getId());
            $fundingRecord->setFundingAccountHolderAddress2($billingAccount->getToken());
            $fundingRecord->setBusinessId($this->businessId);

            $records[] = $fundingRecord;
        }

        return $records;
    }

    /**
     * @param Tenant $user
     * @return array
     */
    protected function getFilteredMerchantsForUser(Tenant $user)
    {
        $result = [];
        foreach ($user->getPaymentAccounts() as $paymentAccount) {
            /** @var PaymentAccountHpsMerchant $merchant */
            foreach ($paymentAccount->getHpsMerchants() as $merchant) {
                if (!isset($result[$merchant->getMerchantName()])) {
                    $result[$merchant->getMerchantName()] = $merchant;
                }
            }
        }

        return array_values($result);
    }
}
