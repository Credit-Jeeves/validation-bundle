<?php

namespace RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper;

use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType as TenantPaymentAccount;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMappe\Exception\PaymentAccountTypeMapException;
use RentJeeves\LandlordBundle\Form\BillingAccountType as LandlordPaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\Form;

/**
 * @DI\Service("payment_account.type.mapper")
 */
class PaymentAccountTypeMapper
{
    /**
     * @param Form $paymentAccountType
     * @return PaymentAccount
     * @throws PaymentAccountTypeMapException
     */
    public function map(Form $paymentAccountType)
    {
        if (TenantPaymentAccount::NAME == $paymentAccountType->getName()) {
            $paymentAccountData = $this->mapTenantAccountTypeForm($paymentAccountType);
        } elseif (LandlordPaymentAccount::NAME == $paymentAccountType->getName()) {
            $paymentAccountData = $this->mapLandlordAccountTypeForm($paymentAccountType);
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
            ->set('ach_deposit_type', $paymentAccountType->get('ACHDepositType')->getData());

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
}
