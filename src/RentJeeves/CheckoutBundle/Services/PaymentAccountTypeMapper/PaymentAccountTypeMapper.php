<?php

namespace RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper;

use RentJeeves\ApiBundle\Forms\Enum\ACHDepositType;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType as TenantPaymentAccount;
use RentJeeves\ApiBundle\Forms\PaymentAccountType as ApiPaymentAccount;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\Exception\PaymentAccountTypeMapException;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\Form;

/**
 * @DI\Service("payment_account.type.mapper")
 */
class PaymentAccountTypeMapper
{
    /**
     * TODO: Using this function makes the code harder to follow.
     * TODO: Instead, please use the concrete mapping functions (below) directly.
     *
     * @param  Form                           $paymentAccountType
     * @return PaymentAccount
     * @throws PaymentAccountTypeMapException
     */
    public function map(Form $paymentAccountType)
    {
        if (TenantPaymentAccount::NAME == $paymentAccountType->getName()) {
            $paymentAccountData = $this->mapTenantAccountTypeForm($paymentAccountType);
        } elseif (ApiPaymentAccount::NAME == $paymentAccountType->getName()) {
            $paymentAccountData = $this->mapApiAccountTypeForm($paymentAccountType);
        } else {
            throw new PaymentAccountTypeMapException('Unknown payment account type mapping');
        }

        return $paymentAccountData;
    }

    public function mapTenantAccountTypeForm(Form $paymentAccountType)
    {
        $paymentAccountData = new PaymentAccount();

        $paymentAccountData->setEntity($paymentAccountType->getData());

        if (PaymentAccountTypeEnum::BANK == $paymentAccountType->get('type')->getData()) {
            $paymentAccountData->set('account_name', $paymentAccountType->get('PayorName')->getData());
        } elseif (PaymentAccountTypeEnum::CARD == $paymentAccountType->get('type')->getData()) {
            $paymentAccountData->set('account_name', $paymentAccountType->get('CardAccountName')->getData());
        }

        $paymentAccountData
            ->set('expiration_month', $paymentAccountType->get('ExpirationMonth')->getData())
            ->set('expiration_year', $paymentAccountType->get('ExpirationYear')->getData())
            ->set('address_choice', $paymentAccountType->get('address_choice')->getData())
            ->set('card_number', $paymentAccountType->get('CardNumber')->getData())
            ->set('routing_number', $paymentAccountType->get('RoutingNumber')->getData())
            ->set('account_number', $paymentAccountType->get('AccountNumber')->getData())
            ->set('ach_deposit_type', $paymentAccountType->get('ACHDepositType')->getData())
            ->set('csc_code', $paymentAccountType->get('VerificationCode')->getData());

        return $paymentAccountData;
    }

    public function mapLandlordAccountTypeForm(Form $paymentAccountType)
    {
        $paymentAccountData = new PaymentAccount();

        $paymentAccountData->setEntity($paymentAccountType->getData());

        $paymentAccountData
            ->set('account_name', $paymentAccountType->get('PayorName')->getData())
            ->set('routing_number', $paymentAccountType->get('RoutingNumber')->getData())
            ->set('account_number', $paymentAccountType->get('AccountNumber')->getData())
            ->set('ach_deposit_type', $paymentAccountType->get('ACHDepositType')->getData());

        return $paymentAccountData;
    }

    public function mapApiAccountTypeForm(Form $paymentAccountType)
    {
        $paymentAccountData = new PaymentAccount();

        $paymentAccountData->setEntity($paymentAccountType->getData());

        $paymentAccountData
            ->set('account_name', $paymentAccountType->get('name')->getData());

        if (PaymentAccountTypeEnum::BANK == $paymentAccountType->get('type')->getData()) {
            $paymentAccountData
                ->set('routing_number', $paymentAccountType->get('bank')->get('routing')->getData())
                ->set('account_number', $paymentAccountType->get('bank')->get('account')->getData())
                ->set(
                    'ach_deposit_type',
                    ACHDepositType::getMapValue(
                        $paymentAccountType->get('bank')->get('type')->getData()
                    )
                );
        } elseif (PaymentAccountTypeEnum::CARD == $paymentAccountType->get('type')->getData()) {
            $expirationDate = $paymentAccountType->get('card')->getData()->getExpiration();
            $paymentAccountData
                ->set('expiration_month', $expirationDate->format('m'))
                ->set('expiration_year', $expirationDate->format('Y'))
                ->set('address_choice', null)
                // TODO add to form billing_address_url for exists addresses
                // $paymentAccountType->get('card')->get('billing_address_url')->getData()
                ->set('card_number', $paymentAccountType->get('card')->get('account')->getData())
                ->set('csc_code', $paymentAccountType->get('card')->get('cvv')->getData());
        }

        return $paymentAccountData;
    }
}
