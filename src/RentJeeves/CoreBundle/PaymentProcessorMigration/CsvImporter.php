<?php

namespace RentJeeves\CoreBundle\PaymentProcessorMigration;

use CreditJeeves\DataBundle\Entity\MailingAddress as Address;
use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManagerInterface;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Deserializer\EnrollmentResponseFileDeserializer;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\AccountResponseRecord;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\ConsumerResponseRecord;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\FundingResponseRecord;
use RentJeeves\DataBundle\Entity\AciCollectPayGroupProfile;
use RentJeeves\DataBundle\Entity\AciCollectPayProfileBilling;
use RentJeeves\DataBundle\Entity\AciCollectPayUserProfile;
use RentJeeves\DataBundle\Entity\AciImportProfileMap;
use RentJeeves\DataBundle\Entity\BillingAccount;
use RentJeeves\DataBundle\Entity\BillingAccountMigration;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\PaymentAccountMigration;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator;

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
     * @var array
     */
    protected $errors = [];

    /**
     * @var Holding
     */
    protected $holding;

    /**
     * @param EntityManagerInterface $em
     * @param EnrollmentResponseFileDeserializer $deserializer
     * @param Validator $validator
     */
    public function __construct(
        EntityManagerInterface $em,
        EnrollmentResponseFileDeserializer $deserializer,
        Validator $validator
    ) {
        $this->em = $em;
        $this->validator = $validator;
        $this->deserializer = $deserializer;
    }

    /**
     * @param string $pathToFile
     * @param Holding $holding
     */
    public function import($pathToFile, Holding $holding = null)
    {
        $this->holding = $holding;
        $records = $this->deserializer->deserialize($pathToFile);
        /** @var ConsumerResponseRecord|FundingResponseRecord|AccountResponseRecord $record */
        foreach ($records as $record) {
            $aciProfileMap = $this->getAciImportProfileMapRepository()->find($record->getProfileId());
            if (false === $this->isValidRecord($record, $aciProfileMap)) {
                continue;
            }
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
        }
    }

    /**
     * @param ConsumerResponseRecord $record
     * @param AciImportProfileMap $aciProfileMap
     */
    protected function importConsumerResponseRecord(ConsumerResponseRecord $record, AciImportProfileMap $aciProfileMap)
    {
        if (null !== $aciProfileMap->getUser()) {
            $newAciProfile = new AciCollectPayUserProfile();
            $newAciProfile->setUser($aciProfileMap->getUser());
            $newAciProfile->setProfileId($record->getProfileId());
            $aciProfileMap->getUser()->setAciCollectPayProfile($newAciProfile);
        } else {
            $newAciProfile = new AciCollectPayGroupProfile();
            $newAciProfile->setGroup($aciProfileMap->getGroup());
            $newAciProfile->setProfileId($record->getProfileId());
            $aciProfileMap->getGroup()->setAciCollectPayProfile($newAciProfile);
        }

        $this->em->persist($newAciProfile);
        $this->em->flush($newAciProfile);
    }

    /**
     * @param AccountResponseRecord $record
     * @param AciImportProfileMap $aciProfileMap
     */
    protected function importAccountResponseRecord(AccountResponseRecord $record, AciImportProfileMap $aciProfileMap)
    {
        if (null !== $user = $aciProfileMap->getUser()) {
            /** AciCollectPayUserProfile $userProfile */
            if (null === $userProfile = $user->getAciCollectPayProfile()) {
                $this->errors[] = sprintf(
                    'AccountResponseRecord: UserProfile for user #%d does not exist',
                    $user->getId()
                );

                return;
            }

            if ($userProfile->hasBillingAccountForDivisionId($record->getDivisionId())) {
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
     * @param AciImportProfileMap $aciProfileMap
     */
    protected function importFundingResponseRecord(FundingResponseRecord $record, AciImportProfileMap $aciProfileMap)
    {
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
            $this->errors[] = sprintf(
                'FundingResponseRecord: PaymentAccount with token#%s not found',
                $token
            );

            return;
        }
        $newPaymentAccount = new PaymentAccount();
        $newPaymentAccount->setPaymentProcessor(PaymentProcessor::ACI);
        $newPaymentAccount->setUser($paymentAccount->getUser());
        if (null === $address = $paymentAccount->getAddress()) {
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
        $newPaymentAccount->setName($record->getFundingAccountNickname());
        $newPaymentAccount->setToken($record->getFundingAccountId()); // PLS CHECK
        $newPaymentAccount->setBankAccountType($paymentAccount->getBankAccountType());

        if ($newPaymentAccount->getType() === PaymentAccountType::BANK) {
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
     * @param AciImportProfileMap $aciProfile
     */
    protected function importGroupFundingResponseRecord(FundingResponseRecord $record, AciImportProfileMap $aciProfile)
    {
        $token = $record->getFundingAccountHolderAddress2();
        if (null === $billingAccount = $this->getBillingAccountRepository()->findOneOrNullByToken($token)) {
            $this->errors[] = sprintf(
                'FundingResponseRecord: BillingAccount with token#%s not found',
                $token
            );

            return;
        }
        $newBillingAccount = new BillingAccount();
        $newBillingAccount->setPaymentProcessor(PaymentProcessor::ACI);
        $newBillingAccount->setGroup($aciProfile->getGroup());
        $newBillingAccount->setToken($record->getFundingAccountId());
        $newBillingAccount->setNickname($record->getFundingAccountNickname());
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
     * @param AciImportProfileMap $aciProfileMap
     *
     * @return bool
     */
    protected function isValidRecord($record, AciImportProfileMap $aciProfileMap)
    {
        $classNameParts = explode('\\', get_class($record));
        $className = end($classNameParts);
        if (null === $aciProfileMap) {
            $this->errors[] = sprintf(
                '%s: AciImportProfileMap with id#%d not found',
                $className,
                $record->getProfileId()
            );

            return false;
        }
        /** @var Tenant $tenant */
        if (null !== $tenant = $aciProfileMap->getUser()) {
            if (false == $this->findContractsByTenant($tenant)) {
                $this->errors[] = sprintf(
                    'AciImportProfileMap#%d: %s: contracts for Holding#%s and Tenant#%d not found',
                    $aciProfileMap->getId(),
                    $className,
                    $this->holding ? $this->holding->getId() : 'all',
                    $tenant->getId()
                );

                return false;
            }
        } else {
            if ($this->holding !== null && $this->holding !== $aciProfileMap->getGroup()->getHolding()) {
                $this->errors[] = sprintf(
                    'AciImportProfileMap#%d: %s: group#%d not related with Holding#%d',
                    $aciProfileMap->getId(),
                    $className,
                    $aciProfileMap->getGroup()->getId(),
                    $this->holding->getId()
                );

                return false;
            }
        }

        /** @var ConstraintViolation $error */
        $errors = $this->validator->validate($record);
        if ($errors->count() > 0) {
            foreach ($errors as $error) {

                $this->errors[] = sprintf(
                    'AciImportProfileMap#%d :%s#%s : %s',
                    $aciProfileMap->getId(),
                    $className,
                    $error->getPropertyPath(),
                    $error->getMessage()
                );
            }

            return false;
        }

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
