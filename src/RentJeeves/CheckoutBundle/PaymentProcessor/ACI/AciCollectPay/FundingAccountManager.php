<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\ACI\AciCollectPay;

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
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use DateTime;

/**
 * @DI\Service("payment.aci_collect_pay.funding_account_manager")
 */
class FundingAccountManager extends AbstractManager
{
    /**
     * @param $fundingAccountId
     * @param $profileId
     * @param FundingAccountData $fundingAccountData
     * @param Contract $contract
     * @return int
     */
    public function modifyFundingAccount(
        $fundingAccountId,
        $profileId,
        FundingAccountData $fundingAccountData,
        Contract $contract
    ) {
        throw new \Exception("Not implement yet");
    }

    /**
     * @param $profileId
     * @param FundingAccountData $fundingAccountData
     * @param Contract $contract
     * @return int
     * @throws PaymentProcessorRuntimeException
     */
    public function addFundingAccount($profileId, FundingAccountData $fundingAccountData, Contract $contract)
    {
        $profile = new RequestModel\Profile();

        $profile->setProfileId($profileId);

        $fundingAccount = $this->prepareFundingAccount($fundingAccountData, $contract);

        $profile->setFundingAccount($fundingAccount);

        $request = new AddFunding($profile);

        $this->paymentProcessor->execute($request);

        if (!$request->getIsSuccessful()) {
            throw new PaymentProcessorRuntimeException($request->getMessages());
        }

        return $request->getModel()->getFundingAccount()->getFundingAccountId();
    }

    /**
     * @param FundingAccountData $fundingAccountData
     * @param Contract $contract
     * @return RequestModel\SubModel\FundingAccount
     */
    public function prepareFundingAccount(FundingAccountData $fundingAccountData, Contract $contract)
    {
        /** @var PaymentAccountEntity $paymentAccount */
        $fundingAccount = new RequestModel\SubModel\FundingAccount();

        $paymentAccount = $fundingAccountData->getEntity();

        $fundingAccount->setNickname($paymentAccount->getName());
        $fundingAccount->setHoldername($fundingAccountData->get('account_name'));

        $address = $contract->getTenant()->getDefaultAddress();

        if (PaymentAccountTypeEnum::CARD == $paymentAccount->getType()) {
            $ccMonth = $fundingAccountData->get('expiration_month');
            $ccYear = $fundingAccountData->get('expiration_year');
            $paymentAccount->setCcExpiration(new DateTime("last day of {$ccYear}-{$ccMonth}"));
            /** @var Address $address */
            if ($fundingAccountData->get('address_choice')) {
                $address = $fundingAccountData->get('address_choice');
            }

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

        $paymentAccount->setAddress($address);

        $fundingAccountAddress = new RequestModel\SubModel\Address();

        $fundingAccountAddress->setAddress1($address->getAddress());
        $fundingAccountAddress->setCity($address->getCity());
        $fundingAccountAddress->setState($address->getArea());
        $fundingAccountAddress->setPostalCode($address->getZip());
        $fundingAccountAddress->setCountryCode($address->getCountry());

        $fundingAccount->setAddress($fundingAccountAddress);

        $fundingAccount->setAccount($account);

        return $fundingAccount;
    }
}
