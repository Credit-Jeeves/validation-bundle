<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay;

use ACI\Client\CollectPay\Enum\BankAccountType;
use CreditJeeves\DataBundle\Entity\MailingAddress as Address;
use CreditJeeves\DataBundle\Entity\User;
use Payum\AciCollectPay\Request\ProfileRequest\AddFunding;
use Payum\AciCollectPay\Request\ProfileRequest\DeleteFunding;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorLogicException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as FundingAccountData;
use RentJeeves\DataBundle\Entity\AciCollectPayGroupProfile;
use RentJeeves\DataBundle\Entity\AciCollectPayUserProfile;
use RentJeeves\DataBundle\Entity\BillingAccount;
use RentJeeves\DataBundle\Entity\BillingAccount as BillingAccountEntity;
use RentJeeves\DataBundle\Entity\PaymentAccount as PaymentAccountEntity;
use Payum\AciCollectPay\Model as RequestModel;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use RentJeeves\DataBundle\Enum\BankAccountType as BankAccountTypeEnum;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

class FundingAccountManager extends AbstractManager
{
    /**
     * @param PaymentAccount $paymentAccount
     *
     * @throws \Exception
     * @throws PaymentProcessorLogicException
     */
    public function removePaymentFundingAccount(PaymentAccount $paymentAccount)
    {
        $profile = $paymentAccount->getUser()->getAciCollectPayProfile();

        if (!$profile) {
            $this->logger->alert(
                sprintf(
                    '[ACI CollectPay Error]: Try to remove payment account "%s" for user #%d without aci profile.',
                    $paymentAccount->getName(),
                    $paymentAccount->getUser()->getId()
                )
            );
            throw new PaymentProcessorLogicException('Can\'t remove payment account without aci profile.');
        }

        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Try to remove funding account "%s" for user with id = "%d" and profile "%d"',
                $paymentAccount->getName(),
                $paymentAccount->getUser()->getId(),
                $profile->getProfileId()
            )
        );

        $fundingAccount = new RequestModel\SubModel\FundingAccount();

        $fundingAccount->setNickname($paymentAccount->getName());
        $fundingAccount->setFundingAccountId($paymentAccount->getToken());

        $this->executeRemoveFundingRequest($profile->getProfileId(), $fundingAccount);

        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Deleted funding account with id = "%s" to profile "%d" for user with id = "%d"',
                $paymentAccount->getToken(),
                $profile->getProfileId(),
                $paymentAccount->getUser()->getId()
            )
        );

        $this->em->remove($paymentAccount);
        $this->em->flush($paymentAccount);
    }

    /**
     * @param AciCollectPayUserProfile $profile
     * @param FundingAccountData $fundingAccountData
     */
    public function addPaymentFundingAccount(AciCollectPayUserProfile $profile, FundingAccountData $fundingAccountData)
    {
        if (!$fundingAccountData->getEntity()->getToken()) {
            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Try to add new funding account for user with id = "%d" and profile "%d"',
                    $profile->getUser()->getId(),
                    $profile->getProfileId()
                )
            );

            $fundingAccount = $this->prepareFundingAccount($fundingAccountData, $profile->getUser());

            $fundingAccountId = $this->executeAddFundingRequest($profile->getProfileId(), $fundingAccount);

            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Added funding account with id = "%s" to profile "%d" for user id = "%d"',
                    $fundingAccountId,
                    $profile->getProfileId(),
                    $profile->getUser()->getId()
                )
            );

            /** @var PaymentAccount $paymentAccount */
            $paymentAccount = $fundingAccountData->getEntity();
            $paymentAccount->setToken($fundingAccountId);
            $paymentAccount->setPaymentProcessor(PaymentProcessor::ACI);

            $this->em->persist($paymentAccount);
            $this->em->flush($paymentAccount);
        } else {
            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Funding account for user id = "%d" and profile "%d" already exists',
                    $profile->getUser()->getId(),
                    $profile->getProfileId()
                )
            );
        }
    }

    /**
     * @param FundingAccountData $fundingAccountData
     * @throws PaymentProcessorLogicException
     */
    public function modifyPaymentFundingAccount(
        FundingAccountData $fundingAccountData
    ) {
        /** @var PaymentAccount $paymentAccount */
        $paymentAccount = $fundingAccountData->getEntity();

        $profile = $paymentAccount->getUser()->getAciCollectPayProfile();

        if (!$profile) {
            $this->logger->alert(
                sprintf(
                    '[ACI CollectPay Error]: Try to modify payment account "%s" for user #%d without aci profile.',
                    $paymentAccount->getName(),
                    $paymentAccount->getUser()->getId()
                )
            );
            throw new PaymentProcessorLogicException('Can\'t modify payment account without aci profile.');
        }

        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Try to modify funding account "%s" for user with id = "%d" and profile "%d"',
                $paymentAccount->getName(),
                $profile->getUser()->getId(),
                $profile->getProfileId()
            )
        );

        $fundingAccount = new RequestModel\SubModel\FundingAccount();

        $fundingAccount->setNickname($paymentAccount->getName());
        $fundingAccount->setFundingAccountId($paymentAccount->getToken());

        $this->executeRemoveFundingRequest($profile->getProfileId(), $fundingAccount);

        $paymentAccount->setToken(null);

        $this->addPaymentFundingAccount($profile, $fundingAccountData);
    }

    /**
     * @param AciCollectPayGroupProfile $profile
     * @param FundingAccountData $fundingAccountData
     */
    public function addBillingFundingAccount(AciCollectPayGroupProfile $profile, FundingAccountData $fundingAccountData)
    {
        if (!$fundingAccountData->getEntity()->getToken()) {
            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Try to add new funding account for group "%s" and profile "%d"',
                    $fundingAccountData->getEntity()->getGroup()->getName(),
                    $profile->getProfileId()
                )
            );

            $fundingAccount = $this->prepareFundingAccount($fundingAccountData);

            $fundingAccountId = $this->executeAddFundingRequest($profile->getProfileId(), $fundingAccount);

            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Added funding account with id = "%s" to profile "%d" for group "%s"',
                    $fundingAccountId,
                    $profile->getProfileId(),
                    $fundingAccountData->getEntity()->getGroup()->getName()
                )
            );

            /** @var BillingAccount $paymentAccount */
            $paymentAccount = $fundingAccountData->getEntity();
            $paymentAccount->setToken($fundingAccountId);
            $paymentAccount->setPaymentProcessor(PaymentProcessor::ACI);

            $this->em->persist($paymentAccount);
            $this->em->flush($paymentAccount);
        } else {
            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Group funding account for group "%s" and profile "%d" already exists',
                    $fundingAccountData->getEntity()->getGroup()->getName(),
                    $profile->getProfileId()
                )
            );
        }
    }

    /**
     * @param  FundingAccountData                   $fundingAccountData
     * @param  User                                 $user
     * @return RequestModel\SubModel\FundingAccount
     *
     * @throws PaymentProcessorInvalidArgumentException
     */
    public function prepareFundingAccount(FundingAccountData $fundingAccountData, User $user = null)
    {
        $fundingAccount = new RequestModel\SubModel\FundingAccount();

        /** @var PaymentAccountEntity|BillingAccountEntity $paymentAccount */
        $paymentAccount = $fundingAccountData->getEntity();

        $fundingAccount->setNickname($paymentAccount->getName());
        $fundingAccount->setHoldername($fundingAccountData->get('account_name'));

        if (PaymentAccountTypeEnum::CARD == $paymentAccount->getType()) {
            $expMonth = $fundingAccountData->get('expiration_month');
            $expYear = $fundingAccountData->get('expiration_year');
            $paymentAccount->setCcExpiration(new \DateTime("last day of {$expYear}-{$expMonth}"));
            $account = new RequestModel\SubModel\CCardAccount();

            $account->setExpMonth($expMonth);
            $account->setExpYear($expYear);
            $account->setCardNumber($fundingAccountData->get('card_number'));
            $account->setCardType(
                RequestModel\Validation\CreditCardChecker::getCreditCardType(
                    $fundingAccountData->get('card_number')
                )
            );
            $account->setSecurityCode($fundingAccountData->get('csc_code'));

        } elseif (PaymentAccountTypeEnum::DEBIT_CARD == $paymentAccount->getType()) {
            $expMonth = $fundingAccountData->get('expiration_month');
            $expYear = $fundingAccountData->get('expiration_year');
            $paymentAccount->setCcExpiration(new \DateTime("last day of {$expYear}-{$expMonth}"));
            $account = new RequestModel\SubModel\DCardAccount();

            $account->setExpMonth($expMonth);
            $account->setExpYear($expYear);
            $account->setCardNumber($fundingAccountData->get('card_number'));
        } elseif (PaymentAccountTypeEnum::BANK == $paymentAccount->getType()) {
            $account = new RequestModel\SubModel\BankAccount();

            $account->setAccountNumber($fundingAccountData->get('account_number'));
            $account->setRoutingNumber($fundingAccountData->get('routing_number'));

            switch ($paymentAccount->getBankAccountType()) {
                case BankAccountTypeEnum::CHECKING:
                    $account->setBankAccountType(BankAccountType::PERSONAL_CHECKING);
                    break;
                case BankAccountTypeEnum::SAVINGS:
                    $account->setBankAccountType(BankAccountType::PERSONAL_SAVINGS);
                    break;
                default:
                    $account->setBankAccountType(BankAccountType::BUSINESS_CHECKING);
                    break;
            }
        } else {
            throw new PaymentProcessorInvalidArgumentException('Unsupported Payment Account Type');
        }

        $fundingAccountAddress = new RequestModel\SubModel\Address();

        /** @var Address $address */
        if ($user) {
            if ($address = $paymentAccount->getAddress()) {
                $paymentAccount->getAddress()->setUser($user);
            }

            if (!$address) {
                $address = $user->getDefaultAddress();
            }

            if (!$address) {
                $address = new Address();
            }

            $fundingAccountAddress->setAddress1((string) $address->getAddress());
            $fundingAccountAddress->setCity((string) $address->getCity());
            $fundingAccountAddress->setState((string) $address->getArea());
            $fundingAccountAddress->setPostalCode((string) $address->getZip());
            $fundingAccountAddress->setCountryCode((string) $address->getCountry());
        } else {
            $fundingAccountAddress->setAddress1((string) $paymentAccount->getGroup()->getStreetAddress1());
            $fundingAccountAddress->setAddress2((string) $paymentAccount->getGroup()->getStreetAddress2());
            $fundingAccountAddress->setCity((string) $paymentAccount->getGroup()->getCity());
            $fundingAccountAddress->setState((string) $paymentAccount->getGroup()->getState());
            $fundingAccountAddress->setPostalCode((string) $paymentAccount->getGroup()->getZip());
            $fundingAccountAddress->setCountryCode((string) $paymentAccount->getGroup()->getCountry());
        }

        $fundingAccount->setAddress($fundingAccountAddress);

        $fundingAccount->setAccount($account);

        return $fundingAccount;
    }

    /**
     * @param int $profileId
     * @param RequestModel\SubModel\FundingAccount $fundingAccount
     * @return int
     *
     * @throws \Exception
     * @throws PaymentProcessorRuntimeException
     */
    protected function executeAddFundingRequest($profileId, RequestModel\SubModel\FundingAccount $fundingAccount)
    {
        $profile = new RequestModel\Profile();

        $profile->setProfileId($profileId);

        $profile->setFundingAccount($fundingAccount);

        $request = new AddFunding($profile);

        try {
            $this->paymentProcessor->execute($request);
        } catch (\Exception $e) {
            $this->logger->alert(
                sprintf(
                    '[ACI CollectPay Add FundingAccount Exception]:Profile(%s):FundingAccount(%s):%s',
                    $profileId,
                    $fundingAccount->getFundingAccountId(),
                    $e->getMessage()
                )
            );
            throw $e;
        }

        if (!$request->getIsSuccessful()) {
            $this->logger->alert(
                sprintf(
                    '[ACI CollectPay Add FundingAccount Error]:Profile(%s):FundingAccount(%s):%s',
                    $profileId,
                    $fundingAccount->getFundingAccountId(),
                    $request->getMessages()
                )
            );
            throw new PaymentProcessorRuntimeException(self::removeDebugInformation($request->getMessages()));
        }

        return $request->getModel()->getFundingAccount()->getFundingAccountId();
    }

    /**
     * @param int $profileId
     * @param RequestModel\SubModel\FundingAccount $fundingAccount
     * @return int
     *
     * @throws \Exception
     * @throws PaymentProcessorRuntimeException
     */
    protected function executeRemoveFundingRequest($profileId, RequestModel\SubModel\FundingAccount $fundingAccount)
    {
        $profile = new RequestModel\Profile();

        $profile->setProfileId($profileId);

        $profile->setFundingAccount($fundingAccount);

        $request = new DeleteFunding($profile);

        try {
            $this->paymentProcessor->execute($request);
        } catch (\Exception $e) {
            $this->logger->alert(
                sprintf(
                    '[ACI CollectPay Remove FundingAccount Exception]:Profile(%s):FundingAccount(%s):%s',
                    $profileId,
                    $fundingAccount->getFundingAccountId(),
                    $e->getMessage()
                )
            );
            throw $e;
        }

        if (!$request->getIsSuccessful()) {
            $this->logger->alert(
                sprintf(
                    '[ACI CollectPay Remove FundingAccount Error]:Profile(%s):FundingAccount(%s):%s',
                    $profileId,
                    $fundingAccount->getFundingAccountId(),
                    $request->getMessages()
                )
            );
            throw new PaymentProcessorRuntimeException(self::removeDebugInformation($request->getMessages()));
        }
    }
}
