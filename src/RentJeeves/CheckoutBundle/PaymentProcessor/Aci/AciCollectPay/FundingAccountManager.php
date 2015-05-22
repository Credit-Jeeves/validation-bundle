<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciCollectPay;

use ACI\Client\CollectPay\Enum\BankAccountType;
use CreditJeeves\DataBundle\Entity\Address;
use JMS\DiExtraBundle\Annotation as DI;
use Payum\AciCollectPay\Request\ProfileRequest\AddFunding;
use RentJeeves\CheckoutBundle\Form\Enum\ACHDepositTypeEnum;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as FundingAccountData;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PaymentAccount as PaymentAccountEntity;
use Payum\AciCollectPay\Model as RequestModel;
use RentJeeves\DataBundle\Entity\UserAwareInterface;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;

/**
 * @DI\Service("payment.aci_collect_pay.funding_account_manager", public=false)
 */
class FundingAccountManager extends AbstractManager
{
    /**
     * @param  int                $fundingAccountId
     * @param  int                $profileId
     * @param  FundingAccountData $fundingAccountData
     * @param  Contract           $contract
     * @return int
     */
    public function modifyFundingAccount(
        $fundingAccountId,
        $profileId,
        FundingAccountData $fundingAccountData,
        Contract $contract
    ) {
        throw new \Exception("modifyFundingAccount is not implement yet for aci_collect_pay.");
    }

    /**
     * @param  int                              $profileId
     * @param  FundingAccountData               $fundingAccountData
     * @param  Contract                         $contract
     * @return int
     * @throws PaymentProcessorRuntimeException
     */
    public function addFundingAccount($profileId, FundingAccountData $fundingAccountData, Contract $contract)
    {
        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Try to add new funding account for user with id = "%d" and profile "%d"',
                $contract->getTenant()->getId(),
                $profileId
            )
        );

        $profile = new RequestModel\Profile();

        $profile->setProfileId($profileId);

        $fundingAccount = $this->prepareFundingAccount($fundingAccountData, $contract);

        $profile->setFundingAccount($fundingAccount);

        $request = new AddFunding($profile);

        try {
            $this->paymentProcessor->execute($request);
        } catch (\Exception $e) {
            $this->logger->alert(sprintf('[ACI CollectPay Critical Error]:%s', $e->getMessage()));
            throw new $e();
        }

        if (!$request->getIsSuccessful()) {
            $this->logger->alert(sprintf('[ACI CollectPay Error]:%s', $request->getMessages()));
            throw new PaymentProcessorRuntimeException($request->getMessages());
        }

        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Added funding account to profile "%d" for user with id = "%d"',
                $request->getModel()->getProfileId(),
                $contract->getTenant()->getId()
            )
        );

        return $request->getModel()->getFundingAccount()->getFundingAccountId();
    }

    /**
     * @param  FundingAccountData                   $fundingAccountData
     * @param  Contract                             $contract
     * @return RequestModel\SubModel\FundingAccount
     */
    public function prepareFundingAccount(FundingAccountData $fundingAccountData, Contract $contract)
    {
        /** @var PaymentAccountEntity $paymentAccount */
        $fundingAccount = new RequestModel\SubModel\FundingAccount();

        $paymentAccount = $fundingAccountData->getEntity();

        $fundingAccount->setNickname($paymentAccount->getName());
        $fundingAccount->setHoldername($fundingAccountData->get('account_name'));

        /** @var Address $address */
        if ($paymentAccount instanceof UserAwareInterface && $address = $paymentAccount->getAddress()) {
            $paymentAccount->getAddress()->setUser($contract->getTenant());
        }

        if (!$address) {
            $address = $contract->getTenant()->getDefaultAddress();
        }

        if (!$address) {
            $address = new Address();
        }

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

            switch ($fundingAccountData->get('ach_deposit_type')) {
                case ACHDepositTypeEnum::CHECKING:
                    $account->setBankAccountType(BankAccountType::PERSONAL_CHECKING);
                    break;
                case ACHDepositTypeEnum::SAVINGS:
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

        $fundingAccountAddress->setAddress1((string) $address->getAddress());
        $fundingAccountAddress->setCity((string) $address->getCity());
        $fundingAccountAddress->setState((string) $address->getArea());
        $fundingAccountAddress->setPostalCode((string) $address->getZip());
        $fundingAccountAddress->setCountryCode($address->getCountry());

        $fundingAccount->setAddress($fundingAccountAddress);

        $fundingAccount->setAccount($account);

        return $fundingAccount;
    }
}
