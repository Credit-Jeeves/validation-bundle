<?php

namespace RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper;

use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType as TenantSiteForm;
use RentJeeves\LandlordBundle\Form\BillingAccountType as LandlordSiteForm;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\Form;

/**
 * @DI\Service("payment_account.type.mapper")
 */
class PaymentAccountTypeMapper
{
    /**
     * @var PaymentAccount
     */
    protected $paymentAccountData;

    /**
     * @param Form $paymentAccountType
     * @return PaymentAccount
     * @throws PaymentAccountTypeMapperException
     */
    public function map(Form $paymentAccountType)
    {
        if (TenantSiteForm::NAME == $paymentAccountType->getName()) {
            $this->mapTenantSiteForm($paymentAccountType);
        } elseif (LandlordSiteForm::NAME == $paymentAccountType->getName()) {
            $this->mapLandlordSiteForm($paymentAccountType);
        } else {
            throw new PaymentAccountTypeMapperException('Unknown payment type mapping');
        }

        return $this->paymentAccountData;
    }

    public function mapTenantSiteForm($paymentAccountType)
    {
        $this->paymentAccountData = new PaymentAccount();

        $this->paymentAccountData->setEntity($paymentAccountType->getData());

        if (PaymentAccountTypeEnum::BANK == $paymentAccountType->get('type')->getData()) {
            $this->paymentAccountData->set('account_name', $paymentAccountType->get('PayorName')->getData());
        } elseif (PaymentAccountTypeEnum::CARD == $paymentAccountType->get('type')->getData()) {
            $this->paymentAccountData->set('account_name', $paymentAccountType->get('CardAccountName')->getData());
        }
        $this->paymentAccountData
            ->set('expiration_month', $paymentAccountType->get('ExpirationMonth')->getData())
            ->set('expiration_year', $paymentAccountType->get('ExpirationYear')->getData())
            ->set('address_choice', $paymentAccountType->get('address_choice')->getData())
            ->set('card_number', $paymentAccountType->get('CardNumber')->getData())
            ->set('routing_number', $paymentAccountType->get('RoutingNumber')->getData())
            ->set('account_number', $paymentAccountType->get('AccountNumber')->getData())
            ->set('ach_deposit_type', $paymentAccountType->get('ACHDepositType')->getData());
    }

    public function mapLandlordSiteForm($paymentAccountType)
    {
        $this->paymentAccountData = new PaymentAccount();

        $this->paymentAccountData->setEntity($paymentAccountType->getData());

        $this->paymentAccountData
            ->set('account_name', $paymentAccountType->get('PayorName')->getData())
            ->set('routing_number', $paymentAccountType->get('RoutingNumber')->getData())
            ->set('account_number', $paymentAccountType->get('AccountNumber')->getData())
            ->set('ach_deposit_type', $paymentAccountType->get('ACHDepositType')->getData());
    }
}
