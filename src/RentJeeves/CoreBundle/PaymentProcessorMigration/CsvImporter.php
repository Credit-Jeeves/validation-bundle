<?php

namespace RentJeeves\CoreBundle\PaymentProcessorMigration;

use CreditJeeves\DataBundle\Entity\MailingAddress as Address;
use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Deserializer\EnrollmentResponseFileDeserializer;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Exception\CsvImportException;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\AccountResponseRecord;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\ConsumerResponseRecord;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\FundingResponseRecord;
use RentJeeves\DataBundle\Entity\AciCollectPayGroupProfile;
use RentJeeves\DataBundle\Entity\AciCollectPayProfileBilling;
use RentJeeves\DataBundle\Entity\AciCollectPayUserProfile;
use RentJeeves\DataBundle\Entity\AciImportProfileMap;
use RentJeeves\DataBundle\Entity\BillingAccount;
use RentJeeves\DataBundle\Entity\BillingAccountMigration;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\PaymentAccountMigration;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator;

/**
 * Service`s name "aci_profiles_importer"
 */
class CsvImporter
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var EnrollmentResponseFileDeserializer
     */
    protected $deserializer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var Holding
     */
    protected $holding;

    /**
     * @param EntityManagerInterface             $em
     * @param EnrollmentResponseFileDeserializer $deserializer
     * @param Validator                          $validator
     * @param LoggerInterface                    $logger
     */
    public function __construct(
        EntityManagerInterface $em,
        EnrollmentResponseFileDeserializer $deserializer,
        Validator $validator,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->validator = $validator;
        $this->deserializer = $deserializer;
        $this->logger = $logger;
    }

    /**
     * @param string  $pathToFile
     * @param Holding $holding
     */
    public function import($pathToFile, Holding $holding = null)
    {
        $this->logger->debug(sprintf('Start Import for file "%s" and Holding#%d', $pathToFile, $holding->getId()));
        $this->holding = $holding;
        $records = $this->deserializer->deserialize($pathToFile);
        /** @var ConsumerResponseRecord|FundingResponseRecord|AccountResponseRecord $record */
        foreach ($records as $record) {
            $aciProfileMap = $this->getAciImportProfileMapRepository()->find($record->getProfileId());
            if (false === $this->isValidRecord($record, $aciProfileMap)) {
                continue;
            }
            try {
                switch (true) {
                    case ($record instanceof ConsumerResponseRecord):
                        $this->importConsumerResponseRecord($record, $aciProfileMap);
                        break;
                    case ($record instanceof AccountResponseRecord):
                        $this->importAccountResponseRecord($record, $aciProfileMap);
                        break;
                    case ($record instanceof FundingResponseRecord):
                        $this->importFundingResponseRecord($record, $aciProfileMap);
                        break;
                    default:
                        break;
                }
            } catch (CsvImportException $e) {
                $message = sprintf(
                    'Can`t import %s: %s',
                    end(explode('\\', get_class($record))),
                    $e->getMessage()
                );
                $this->logger->error($message);
                $this->errors[] = $message;

                continue;
            }
        }
    }

    /**
     * @param ConsumerResponseRecord $record
     * @param AciImportProfileMap    $aciProfileMap
     */
    protected function importConsumerResponseRecord(ConsumerResponseRecord $record, AciImportProfileMap $aciProfileMap)
    {
        $this->logger->debug(sprintf('Importing Consumer record for AciImportProfileMap#%d', $aciProfileMap->getId()));
        if (null !== $aciProfileMap->getUser()) {
            $newAciProfile = new AciCollectPayUserProfile();
            $newAciProfile->setUser($aciProfileMap->getUser());
            $newAciProfile->setProfileId($record->getConsumerProfileId());
            $aciProfileMap->getUser()->setAciCollectPayProfile($newAciProfile);
        } else {
            $group = $aciProfileMap->getGroup();
            $aciDepositAccount = $this->getAciDepositAccount($group);
            $aciDepositAccountId = $aciDepositAccount->getId();

            $newAciProfile = new AciCollectPayGroupProfile();
            $newAciProfile->setGroup($group);
            $newAciProfile->setProfileId($record->getConsumerProfileId());
            $newAciProfile->setBillingAccountNumber(
                $this->getGroupBillingAccountNumber($group, $aciDepositAccountId)
            );
            $aciProfileMap->getGroup()->setAciCollectPayProfile($newAciProfile);
        }

        $this->em->persist($newAciProfile);
        $this->em->flush($newAciProfile);
    }

    /**
     * @param Group  $group
     * @param string $divisionId
     *
     * @return string
     */
    protected function getGroupBillingAccountNumber(Group $group, $divisionId)
    {
        return sprintf('%s%s', $divisionId, $group->getId());
    }

    /**
     * @param Group $group
     *
     * @throws CsvImportException when group has 0 or more then 1 DAs
     *
     * @return DepositAccount
     */
    protected function getAciDepositAccount(Group $group)
    {
        $aciAccounts = [];
        foreach ($group->getDepositAccounts() as $account) {
            if ($account->getPaymentProcessor() === PaymentProcessor::ACI) {
                $aciAccounts[] = $account;
            }
        }

        $accountsFound = count($aciAccounts);
        if ($accountsFound < 1) {
            throw new CsvImportException('No ACI Deposit Account found for ' . $group->getName());
        } elseif ($accountsFound > 1) {
            throw new CsvImportException('More than one ACI Deposit Account found for ' . $group->getName());
        }

        return $aciAccounts[0];
    }

    /**
     * @param AccountResponseRecord $record
     * @param AciImportProfileMap   $aciProfileMap
     */
    protected function importAccountResponseRecord(AccountResponseRecord $record, AciImportProfileMap $aciProfileMap)
    {
        $this->logger->debug(sprintf('Importing Account record for AciImportProfileMap#%d', $aciProfileMap->getId()));
        if (null !== $user = $aciProfileMap->getUser()) {
            /** AciCollectPayUserProfile $userProfile */
            if (null === $userProfile = $user->getAciCollectPayProfile()) {
                $message = sprintf(
                    'AccountResponseRecord: UserProfile for user #%d does not exist',
                    $user->getId()
                );
                $this->errors[] = $message;
                $this->logger->debug($message);

                return;
            }

            if ($userProfile->hasBillingAccountForDivisionId($record->getDivisionId())) {
                $message = sprintf(
                    'AccountResponseRecord: UserProfile#%d has BillingAccount for DivisionId = "%s"',
                    $userProfile->getId(),
                    $record->getDivisionId()
                );
                $this->errors[] = $message;
                $this->logger->debug($message);

                return;
            }

            $newAciProfileBilling = new AciCollectPayProfileBilling();
            $newAciProfileBilling->setProfile($userProfile);
            $newAciProfileBilling->setDivisionId($record->getDivisionId());
            $newAciProfileBilling->setBillingAccountNumber($record->getBillingAccountNumber());
            $userProfile->addAciCollectPayProfileBilling($newAciProfileBilling);

            $this->em->persist($newAciProfileBilling);
            $this->em->flush($newAciProfileBilling);
        } else {
            $groupProfile = $aciProfileMap->getGroup()->getAciCollectPayProfile();
            $groupProfile->setBillingAccountNumber($record->getBillingAccountNumber());

            $this->em->flush($groupProfile);
        }
    }

    /**
     * @param FundingResponseRecord $record
     * @param AciImportProfileMap   $aciProfileMap
     */
    protected function importFundingResponseRecord(FundingResponseRecord $record, AciImportProfileMap $aciProfileMap)
    {
        $this->logger->debug(sprintf('Importing Funding record for AciImportProfileMap#%d', $aciProfileMap->getId()));
        if (null !== $aciProfileMap->getUser()) {
            $this->importUserFundingResponseRecord($record);
        } else {
            $this->importGroupFundingResponseRecord($record, $aciProfileMap);
        }
    }

    /**
     * @param FundingResponseRecord $record
     */
    protected function importUserFundingResponseRecord(FundingResponseRecord $record)
    {
        $token = $record->getFundingAccountHolderAddress2();
        /** @var PaymentAccount $paymentAccount */
        if (null === $paymentAccount = $this->getPaymentAccountRepository()->findOneOrNullByToken($token)) {
            $message = sprintf(
                'FundingResponseRecord: PaymentAccount with token#%s not found',
                $token
            );
            $this->errors[] = $message;
            $this->logger->debug($message);

            return;
        }
        $newPaymentAccount = new PaymentAccount();
        $newPaymentAccount->setPaymentProcessor(PaymentProcessor::ACI);
        $newPaymentAccount->setUser($paymentAccount->getUser());
        if (null === $address = $paymentAccount->getAddress()) {
            $this->logger->debug(
                sprintf(
                    'FundingResponseRecord: PaymentAccount#%d doesn`t have MailingAddress.Creating new MailingAddress.',
                    $paymentAccount->getId()
                )
            );
            /** @TODO: need example for fundingAccountHolderAddress1 */
            $address = new Address();
            $address->setUser($paymentAccount->getUser());
            $address->setZip($record->getFundingAccountHolderZipCode());
            $address->setCity($record->getFundingAccountHolderCity());
            $address->setArea($record->getFundingAccountHolderState());
            $address->setCountry($record->getFundingAccountHolderCountryCode());
            $address->setIsDefault(false);

            $this->em->persist($address);
        }
        $newPaymentAccount->setAddress($address);
        $newPaymentAccount->setType($paymentAccount->getType());
        $newPaymentAccount->setName($paymentAccount->getName());
        $newPaymentAccount->setLastFour($paymentAccount->getLastFour());
        $newPaymentAccount->setToken($record->getFundingAccountId());
        $newPaymentAccount->setBankAccountType($paymentAccount->getBankAccountType());

        if ($newPaymentAccount->getType() === PaymentAccountType::CARD) {
            $expirationDate = new \DateTime(sprintf(
                '%s-%s-28',
                $record->getCardExpirationYear(),
                $record->getCardExpirationMonth()
            ));
            $expirationDate->modify('last day of this month');
            $newPaymentAccount->setCcExpiration($expirationDate);
        }

        $this->em->persist($newPaymentAccount);

        $newPaymentMigration = new PaymentAccountMigration();
        $newPaymentMigration->setHeartlandPaymentAccount($paymentAccount);
        $newPaymentMigration->setAciPaymentAccount($newPaymentAccount);

        $this->em->persist($newPaymentMigration);
        $this->em->flush();
    }

    /**
     * @param FundingResponseRecord $record
     * @param AciImportProfileMap   $aciProfile
     */
    protected function importGroupFundingResponseRecord(FundingResponseRecord $record, AciImportProfileMap $aciProfile)
    {
        $token = $record->getFundingAccountHolderAddress2();
        if (null === $billingAccount = $this->getBillingAccountRepository()->findOneOrNullByToken($token)) {
            $message = sprintf(
                'FundingResponseRecord: BillingAccount with token#%s not found',
                $token
            );
            $this->errors[] = $message;
            $this->logger->debug($message);

            return;
        }
        $newBillingAccount = new BillingAccount();
        $newBillingAccount->setPaymentProcessor(PaymentProcessor::ACI);
        $newBillingAccount->setGroup($aciProfile->getGroup());
        $newBillingAccount->setToken($record->getFundingAccountId());
        $newBillingAccount->setNickname($billingAccount->getName());
        $newBillingAccount->setLastFour($billingAccount->getLastFour());
        $newBillingAccount->setBankAccountType($billingAccount->getBankAccountType());
        $newBillingAccount->setIsActive($billingAccount->getIsActive());

        $this->em->persist($newBillingAccount);

        $newBillingAccountMigration = new BillingAccountMigration();
        $newBillingAccountMigration->setHeartlandBillingAccount($billingAccount);
        $newBillingAccountMigration->setAciBillingAccount($newBillingAccount);

        $this->em->persist($newBillingAccountMigration);
        $this->em->flush();
    }

    /**
     * @param ConsumerResponseRecord|FundingResponseRecord|AccountResponseRecord $record
     * @param AciImportProfileMap                                                $aciProfileMap
     *
     * @return bool
     */
    protected function isValidRecord($record, AciImportProfileMap $aciProfileMap)
    {
        $classNameParts = explode('\\', get_class($record));
        $className = end($classNameParts);

        $this->logger->debug(sprintf('Validating %s with profileId#%s', $className, $record->getProfileId()));

        if (null === $aciProfileMap) {
            $message = sprintf(
                '%s: AciImportProfileMap with id#%d not found',
                $className,
                $record->getProfileId()
            );
            $this->errors[] = $message;
            $this->logger->debug($message);

            return false;
        }
        $this->logger->debug(sprintf('Found AciImportProfileMap#%d', $aciProfileMap->getId()));

        /** @var Tenant $tenant */
        if (null !== $tenant = $aciProfileMap->getUser()) {
            if (false == $this->findContractsByTenant($tenant)) {
                $message = sprintf(
                    'AciImportProfileMap#%d: %s: contracts for Holding#%s and Tenant#%d not found',
                    $aciProfileMap->getId(),
                    $className,
                    $this->holding ? $this->holding->getId() : 'all',
                    $tenant->getId()
                );
                $this->errors[] = $message;
                $this->logger->debug($message);

                return false;
            }
        } else {
            if ($this->holding !== null && $this->holding !== $aciProfileMap->getGroup()->getHolding()) {
                $message = sprintf(
                    'AciImportProfileMap#%d: %s: group#%d not related with Holding#%d',
                    $aciProfileMap->getId(),
                    $className,
                    $aciProfileMap->getGroup()->getId(),
                    $this->holding->getId()
                );
                $this->errors[] = $message;
                $this->logger->debug($message);

                return false;
            }
        }

        /** @var ConstraintViolation $error */
        $errors = $this->validator->validate($record);
        if ($errors->count() > 0) {
            foreach ($errors as $error) {
                $message = sprintf(
                    'AciImportProfileMap#%d :%s#%s : %s',
                    $aciProfileMap->getId(),
                    $className,
                    $error->getPropertyPath(),
                    $error->getMessage()
                );
                $this->errors[] = $message;
                $this->logger->debug($message);
            }

            return false;
        }
        $this->logger->debug(sprintf('AciImportProfileMap#%d is valid', $aciProfileMap->getId()));

        return true;
    }

    /**
     * @param Tenant $tenant
     *
     * @return array
     */
    protected function findContractsByTenant(Tenant $tenant)
    {
        if (null !== $this->holding) {
            return $this->getContractRepository()->findBy([
                'holding' => $this->holding,
                'tenant' => $tenant
            ]);
        } else {
            return $this->getContractRepository()->findBy(['tenant' => $tenant]);
        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\AciImportProfileMapRepository
     */
    protected function getAciImportProfileMapRepository()
    {
        return $this->em->getRepository('RjDataBundle:AciImportProfileMap');
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\ContractRepository
     */
    protected function getContractRepository()
    {
        return $this->em->getRepository('RjDataBundle:Contract');
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\PaymentAccountRepository
     */
    protected function getPaymentAccountRepository()
    {
        return $this->em->getRepository('RjDataBundle:PaymentAccount');
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\BillingAccountRepository
     */
    protected function getBillingAccountRepository()
    {
        return $this->em->getRepository('RjDataBundle:BillingAccount');
    }
}
