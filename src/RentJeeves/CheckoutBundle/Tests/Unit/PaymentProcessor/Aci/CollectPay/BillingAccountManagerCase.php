<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\Aci\CollectPay;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\BillingAccountManager;
use RentJeeves\DataBundle\Entity\AciCollectPayProfileBilling;
use RentJeeves\DataBundle\Entity\AciCollectPayUserProfile;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class BillingAccountManagerCase extends  \PHPUnit_Framework_TestCase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function addBillingAccountShouldHaveDivisionId()
    {
        $em = $this->getEntityManagerMock();
        $logger = $this->getLoggerMock();
        $msg = '[ACI CollectPay Info]:Billing account for profile "0" and deposit account id = "0" already exists';
        $logger->expects($this->once())->method('debug')->with($msg);
        $containerAwareRegistryRegister = $this->getContainerAwareRegistryMock();
        $billingAccountManager = new BillingAccountManager($em, $containerAwareRegistryRegister, $logger);

        $aciUserProfile = new AciCollectPayUserProfile();
        $depositAccount = new DepositAccount();
        $depositAccount->setMerchantName('test');
        $aciCollectProfileBilling = new AciCollectPayProfileBilling();
        $aciCollectProfileBilling->setDivisionId('test');
        $aciUserProfile->addAciCollectPayProfileBilling($aciCollectProfileBilling);
        $billingAccountManager->addBillingAccount($aciUserProfile, $depositAccount);
    }

    /**
     * @test
     */
    public function addBillingAccountShouldNotHaveDivisionIdAndShouldBeSuccess()
    {
        $em = $this->getEntityManagerMock();
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');
        $logger = $this->getLoggerMock();
        $containerAwareRegistryRegister = $this->getContainerAwareRegistryMock();
        $payment = $this->getPayment();
        $payment->expects($this->once())->method('execute');
        $containerAwareRegistryRegister->expects($this->once())->method('getPayment')->willReturn($payment);

        $billingAccountManager = new BillingAccountManager($em, $containerAwareRegistryRegister, $logger);

        $aciUserProfile = new AciCollectPayUserProfile();
        $depositAccount = new DepositAccount();
        $group = new Group();
        $depositAccount->setGroup($group);
        $tenant = new Tenant();
        $aciUserProfile->setUser($tenant);
        $aciUserProfile->setProfileId(33);
        $depositAccount->setMerchantName('test_sss');
        $aciCollectProfileBilling = new AciCollectPayProfileBilling();
        $aciCollectProfileBilling->setDivisionId('test');
        $aciUserProfile->addAciCollectPayProfileBilling($aciCollectProfileBilling);
        $billingAccountManager->addBillingAccount($aciUserProfile, $depositAccount);
    }
}

