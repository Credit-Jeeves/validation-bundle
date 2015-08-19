<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay;

use ACI\Client\CollectPay\Enum\BankAccountType;
use CreditJeeves\DataBundle\Entity\Address;
use CreditJeeves\DataBundle\Entity\User;
use Payum\AciCollectPay\Request\ProfileRequest\AddFunding;
use Payum\AciCollectPay\Request\ProfileRequest\ModifyFunding;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentAccountInterface;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as FundingAccountData;
use RentJeeves\DataBundle\Entity\GroupAwareInterface;
use RentJeeves\DataBundle\Entity\BillingAccount as BillingAccountEntity;
use RentJeeves\DataBundle\Entity\PaymentAccount as PaymentAccountEntity;
use Payum\AciCollectPay\Model as RequestModel;
use RentJeeves\DataBundle\Entity\UserAwareInterface;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use RentJeeves\DataBundle\Enum\BankAccountType as BankAccountTypeEnum;

class FundingAccountManager extends AbstractManager
{
    /**
     * @param  int                $fundingAccountId
     * @param  int                $profileId
     * @param  FundingAccountData $fundingAccountData
     * @param  User               $user
     * @return int
     *
     * @throws \Exception
     */
    public function modifyFundingAccount(
        $fundingAccountId,
        $profileId,
        FundingAccountData $fundingAccountData,
        User $user = null
    ) {
        $fundingAccount = $this->prepareFundingAccount($fundingAccountData, $user);
        $fundingAccount->setFundingAccountId($fundingAccountId);

        $profile = new RequestModel\Profile();
        $profile->setProfileId($profileId);
        $profile->setFundingAccount($fundingAccount);

        $request = new ModifyFunding($profile);

        try {
            $this->paymentProcessor->execute($request);
        } catch (\Exception $e) {
            $this->logger->alert(sprintf('[ACI CollectPay Critical Error]:%s', $e->getMessage()));
            throw $e;
        }

        if (!$request->getIsSuccessful()) {
            $this->logger->alert(sprintf('[ACI CollectPay Error]:%s', $request->getMessages()));
            throw new PaymentProcessorRuntimeException(self::removeDebugInformation($request->getMessages()));
        }

        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Modify funding account with id = "%s" to profile "%d" for user with id = "%d"',
                $fundingAccountId,
                $profileId,
                $user->getId()
            )
        );

        return $fundingAccountId;
    }

    /**
     * @param  int                              $profileId
     * @param  FundingAccountData               $fundingAccountData
     * @param  User                             $user
     * @return int
     * @throws PaymentProcessorRuntimeException|PaymentProcessorInvalidArgumentException
     */
    public function addFundingAccount($profileId, FundingAccountData $fundingAccountData, User $user = null)
    {
        if (!$fundingAccountData->getEntity() instanceof PaymentAccountInterface) {
            throw new PaymentProcessorInvalidArgumentException('Entity should implement PaymentAccountInterface');
        }

        if ($fundingAccountData->getEntity() instanceof UserAwareInterface) {
            return $this->addPaymentFundingAccount($profileId, $fundingAccountData, $user);
        } elseif ($fundingAccountData->getEntity() instanceof GroupAwareInterface) {
            return $this->addBillingFundingAccount($profileId, $fundingAccountData);
        } else {
            throw new PaymentProcessorInvalidArgumentException('Unsupported Payment Account Type');
        }
    }

    /**
     * @param int $profileId
     * @param FundingAccountData $fundingAccountData
     * @param User $user
     * @return int
     */
    protected function addPaymentFundingAccount($profileId, FundingAccountData $fundingAccountData, User $user)
    {
        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Try to add new funding account for user with id = "%d" and profile "%d"',
                $user->getId(),
                $profileId
            )
        );

        $fundingAccount = $this->prepareFundingAccount($fundingAccountData, $user);

        $fundingAccountId = $this->executeRequest($profileId, $fundingAccount);

        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Added funding account with id = "%s" to profile "%d" for user with id = "%d"',
                $fundingAccountId,
                $profileId,
                $user->getId()
            )
        );

        return $fundingAccountId;
    }

    /**
     * @param int $profileId
     * @param FundingAccountData $fundingAccountData
     * @return int
     */
    protected function addBillingFundingAccount($profileId, FundingAccountData $fundingAccountData)
    {
        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Try to add new funding account for group "%s" and profile "%d"',
                $fundingAccountData->getEntity()->getGroup()->getName(),
                $profileId
            )
        );

        $fundingAccount = $this->prepareFundingAccount($fundingAccountData);

        $fundingAccountId = $this->executeRequest($profileId, $fundingAccount);

        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Added funding account with id = "%s" to profile "%d" for group "%s"',
                $fundingAccountId,
                $profileId,
                $fundingAccountData->getEntity()->getGroup()->getName()
            )
        );

        return $fundingAccountId;
    }

    /**
     * @param  FundingAccountData                   $fundingAccountData
     * @param  User                                 $user
     * @return RequestModel\SubModel\FundingAccount
     */
    public function prepareFundingAccount(FundingAccountData $fundingAccountData, User $user = null)
    {
        $fundingAccount = new RequestModel\SubModel\FundingAccount();

        /** @var PaymentAccountEntity|BillingAccountEntity $paymentAccount */
        $paymentAccount = $fundingAccountData->getEntity();

        $fundingAccount->setNickname($paymentAccount->getName());
        $fundingAccount->setHoldername($fundingAccountData->get('account_name'));

        if (PaymentAccountTypeEnum::CARD == $paymentAccount->getType()) {
            $ccMonth = $fundingAccountData->get('expiration_month');
            $ccYear = $fundingAccountData->get('expiration_year');
            $paymentAccount->setCcExpiration(new \DateTime("last day of {$ccYear}-{$ccMonth}"));
            $account = new RequestModel\SubModel\CCardAccount();

            $account->setExpMonth($ccMonth);
            $account->setExpYear($ccYear);
            $account->setCardNumber($fundingAccountData->get('card_number'));
            $account->setCardType(
                RequestModel\Validation\CreditCardChecker::getCreditCardType(
                    $fundingAccountData->get('card_number')
                )
            );
            $account->setSecurityCode($fundingAccountData->get('csc_code'));

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
     */
    protected function executeRequest($profileId, RequestModel\SubModel\FundingAccount $fundingAccount)
    {
        $profile = new RequestModel\Profile();

        $profile->setProfileId($profileId);

        $profile->setFundingAccount($fundingAccount);

        $request = new AddFunding($profile);

        try {
            $this->paymentProcessor->execute($request);
        } catch (\Exception $e) {
            $this->logger->alert(sprintf('[ACI CollectPay Critical Error]:%s', $e->getMessage()));
            throw $e;
        }

        if (!$request->getIsSuccessful()) {
            $this->logger->alert(sprintf('[ACI CollectPay Error]:%s', $request->getMessages()));
            throw new PaymentProcessorRuntimeException(self::removeDebugInformation($request->getMessages()));
        }

        return $request->getModel()->getFundingAccount()->getFundingAccountId();
    }
}
