<?php
namespace CreditJeeves\DataBundle\Tests\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\BillingAccount;
use RentJeeves\DataBundle\Enum\BankAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\TestBundle\BaseTestCase;

class GroupCase extends BaseTestCase
{
    /**
     * @param Group $group
     * @param string $paymentProcessor
     * @param string $nickName
     */
    protected function createNewBillingAccount(Group $group, $paymentProcessor, $nickName)
    {
        $billingAccount = new BillingAccount();
        $billingAccount->setGroup($group);
        $billingAccount->setToken('111111');
        $billingAccount->setNickname($nickName);
        $billingAccount->setBankAccountType(BankAccountType::CHECKING);
        $billingAccount->setPaymentProcessor($paymentProcessor);
        $group->addBillingAccount($billingAccount);
        $this->getEntityManager()->persist($billingAccount);
        $this->getEntityManager()->flush();
    }

    /**
     * @test
     */
    public function shouldGetBillingAccountsByPaymentProcessor()
    {
        $this->load(true);
        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneByName('Test Rent Group');
        $this->assertNotEmpty($group);
        $groupSettings = $group->getGroupSettings();
        $this->assertNotEmpty($groupSettings);
        $this->assertEquals(PaymentProcessor::HEARTLAND, $groupSettings->getPaymentProcessor());
        $this->assertCount(0, $group->getBillingAccounts());
        $groupSettings->setPaymentProcessor(PaymentProcessor::ACI);
        $this->getEntityManager()->persist($groupSettings);
        $this->getEntityManager()->flush();
        $this->assertCount(0, $group->getBillingAccounts());
        $this->createNewBillingAccount($group, PaymentProcessor::ACI, 'aciFirst');
        $this->createNewBillingAccount($group, PaymentProcessor::HEARTLAND, 'heartlandFirst');
        $this->createNewBillingAccount($group, PaymentProcessor::HEARTLAND, 'heartlandSecond');

        $this->assertCount(3, $group->getBillingAccounts());
        $this->assertCount(1, $group->getBillingAccountByCurrentPaymentProcessor());

        $groupSettings->setPaymentProcessor(PaymentProcessor::HEARTLAND);
        $this->getEntityManager()->flush();

        $this->assertCount(2, $group->getBillingAccountByCurrentPaymentProcessor());
    }
}
