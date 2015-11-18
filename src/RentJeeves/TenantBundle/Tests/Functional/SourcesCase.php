<?php
namespace RentJeeves\TenantBundle\Tests\Functional;

use ACI\Utils\OldProfilesStorage;
use CreditJeeves\DataBundle\Entity\Group;
use Payum\AciCollectPay\Model\Profile;
use Payum\AciCollectPay\Request\ProfileRequest\DeleteProfile;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorAciCollectPay;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as PaymentAccountData;
use RentJeeves\DataBundle\Enum\BankAccountType;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use Symfony\Component\Config\FileLocator;

class SourcesCase extends BaseTestCase
{
    use OldProfilesStorage;
    /**
     * @var FileLocator
     */
    protected $fixtureLocator;

    /**
     * @param Tenant $tenant
     * @return PaymentAccount
     */
    protected function prepareFixturesAciCollectPay(Tenant $tenant)
    {
        $this->fixtureLocator = new FileLocator(
            [__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Fixtures']
        );

        // Test Rent Group
        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneByCode('DXC6KXOAGX');
        /* Prepare Group */
        $group->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);

        $depositAccount = new DepositAccount($group);
        $depositAccount->setPaymentProcessor($group->getGroupSettings()->getPaymentProcessor());
        $depositAccount->setType(DepositAccountType::RENT);
        $depositAccount->setMerchantName(564075);

        $group->addDepositAccount($depositAccount);

        /* Create Payment Accounts */
        /** @var PaymentProcessorAciCollectPay $paymentProcessor */
        $paymentProcessor = $this->getContainer()->get('payment_processor.aci_collect_pay');

        $paymentAccount = new PaymentAccount();

        $paymentAccount->setUser($tenant);
        $paymentAccount->setPaymentProcessor(PaymentProcessor::ACI);
        $paymentAccount->setType(PaymentAccountTypeEnum::BANK);
        $paymentAccount->setName('Test ACI Bank');
        $paymentAccount->setBankAccountType(BankAccountType::CHECKING);

        $paymentAccountData = new PaymentAccountData();

        $paymentAccountData->setEntity($paymentAccount);

        $paymentAccountData
            ->set('account_name', $tenant->getFullName())
            ->set('routing_number', '063113057')
            ->set('account_number', '123245678');

        $paymentProcessor->registerPaymentAccount($paymentAccountData, $depositAccount);

        $this->getEntityManager()->refresh($tenant);

        $this->setOldProfileId(
            md5($tenant->getId()),
            $tenant->getAciCollectPayProfileId()
        );

        $this->getEntityManager()->refresh($paymentAccount);

        return $paymentAccount;
    }

    /**
     * @test
     */
    public function delAci()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');

        /** @var Tenant $tenant */
        $tenant = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:Tenant')
            ->findOneByEmail('tenant11@example.com');

        $this->assertNotEmpty($tenant, 'Check fixtures, tenant with email "tenant11@example.com" should be exist');

        $createdPaymentAccount = $this->prepareFixturesAciCollectPay($tenant);
        $id = $createdPaymentAccount->getId();

        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('rent.sources');

        $this->session->wait($this->timeout, "jQuery('#payment-account-table').length");
        $this->assertNotNull(
            $row = $this->page->find('css', '#payment-account-table tbody tr td:contains(\'Test ACI Bank\')'),
            'Should be displayed row with our added payment account'
        );
        $this->assertNotEmpty(
            $rows = $this->page->findAll('css', '#payment-account-table tbody tr'),
            'Should be displayed rows with payment account and have at least 1 added new payment account'
        );
        $rows[count($rows) - 1]->clickLink('delete'); // remove last account

        $this->session->wait($this->timeout, "jQuery('#payment-account-delete:visible').length");

        $this->page->clickLink('payment_account.delete.yes');

        $this->session->wait(
            $this->timeout,
            "jQuery('#payment-account-table').length"
        );

        $beforeRemoveCount = count($rows);
        $rows = $this->page->findAll('css', '#payment-account-table tbody tr');
        $this->assertCount($beforeRemoveCount - 1, $rows, 'Should be removed one row');
        $this->assertNull(
            $row = $this->page->find('css', '#payment-account-table tbody tr td:contains(\'Test ACI Bank\')'),
            'Should be removed row with our added payment account'
        );

        $this->getEntityManager()->clear(); // should refresh cache

        $result = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:PaymentAccount')
            ->find($id);
        $this->assertTrue(
            is_null($result),
            sprintf('Aci payment account "%d" should be removed.', $id)
        );

        $this->logout();
    }

    /**
     * @test
     */
    public function delHeartland()
    {
        $this->load(false);
        $this->setDefaultSession('selenium2');
        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('rent.sources');

        $this->session->wait($this->timeout, "jQuery('#payment-account-table').length");
        $this->assertNotNull($rows = $this->page->findAll('css', '#payment-account-table tbody tr'));
        $rowsCount = count($rows);
        $this->asserttrue(1 < $rowsCount);
        $rowsCount -= 1;
        $rows[$rowsCount]->clickLink('delete');
        $this->session->wait($this->timeout, "jQuery('#payment-account-delete:visible').length");
        $this->page->clickLink('payment_account.delete.yes');

        $this->session->wait($this->timeout, ($rowsCount) . " == jQuery('#payment-account-table tbody tr').length");
        $this->assertNotNull($rows = $this->page->findAll('css', '#payment-account-table tbody tr'));
        $this->assertCount($rowsCount, $rows);
        $this->logout();
    }

    /**
     * @test
     */
    public function checkEmailNotifyWhenRemoveContract()
    {
        $this->markTestSkipped('Temporary remove delete contract function');
        $this->setDefaultSession('selenium2');
        $this->load(false);
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($rows = $this->page->findAll('css', '.properties-table tbody tr'));
        $this->assertCount(4, $rows);

        $this->assertNotNull($contract = $this->page->findAll('css', '.contract-delete'));
        $contract[0]->click();
        $this->session->wait($this->timeout, "jQuery('#contract-delete:visible').length");
        $this->assertNotNull($delete = $this->page->find('css', '#button-contract-delete'));
        $delete->click();
        $this->session->wait($this->timeout, "2 == jQuery('.properties-table tbody tr').length");
        $this->assertNotNull($rows = $this->page->findAll('css', '.properties-table tbody tr'));
        $this->assertCount(3, $rows);
        $this->logout();

        //Check email notify landlord about removed contract by tenant
        $this->assertCount(1, $this->getEmails(), 'Wrong number of emails');
    }

    /**
     * @param int $profileId
     */
    protected function deleteAciCollectPayProfile($profileId)
    {
        $profile = new Profile();

        $profile->setProfileId($profileId);

        $request = new DeleteProfile($profile);

        $this->getContainer()->get('payum')->getPayment('aci_collect_pay')->execute($request);

        $this->assertTrue($request->getIsSuccessful());

        $this->unsetOldProfileId($profileId);
    }

    /**
     * @test
     */
    public function shouldShowErrorIfTryDeleteSourceWithPendingOrder()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');

        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('rent.sources');

        $this->session->wait($this->timeout, "jQuery('#payment-account-table').length");
        $this->assertNotNull(
            $row = $this->page->find('css', '#payment-account-table tbody tr td:contains(\'Card\')'),
            'Should be displayed row with name Card'
        );
        $rows = $this->page->findAll('css', '#payment-account-table tbody tr');
        $this->assertEquals(3, count($rows), 'Should be show 3 rows with payment account');
        $rows[0]->clickLink('delete');

        $this->session->wait($this->timeout, "jQuery('#payment-account-delete:visible').length");
        $this->page->clickLink('payment_account.delete.yes');

        $this->session->wait(
            $this->timeout,
            "jQuery('#payment-account-table').length"
        );

        $this->assertNotNull(
            $message = $this->page->find('css', 'h3.error_message'),
            'Error message not showing'
        );
        $this->assertEquals('payment_source.remove.error', $message->getHtml());
    }

    /**
     * @test
     */
    public function shouldShowErrorIfTryDeleteSourceWithActivePayment()
    {
        $this->load(true);

        $paymentAccount = $this->getEntityManager()
            ->getRepository('RjDataBundle:PaymentAccount')->findOneBy(['name' => 'Bank']);
        $payment = $paymentAccount->getPayments()->first();
        $payment->setStatus(PaymentStatus::ACTIVE);

        $this->getEntityManager()->flush();

        $this->setDefaultSession('selenium2');

        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('rent.sources');

        $this->session->wait($this->timeout, "jQuery('#payment-account-table').length");
        $this->assertNotNull(
            $row = $this->page->find('css', '#payment-account-table tbody tr td:contains(\'Bank\')'),
            'Should be displayed row with name Bank'
        );
        $rows = $this->page->findAll('css', '#payment-account-table tbody tr');
        $this->assertEquals(3, count($rows), 'Should be show 3 rows with payment account');
        $rows[1]->clickLink('delete');

        $this->session->wait($this->timeout, "jQuery('#payment-account-delete:visible').length");
        $this->page->clickLink('payment_account.delete.yes');

        $this->session->wait(
            $this->timeout,
            "jQuery('#payment-account-table').length"
        );

        $this->assertNotNull(
            $message = $this->page->find('css', 'h3.error_message'),
            'Error message not showing'
        );
        $this->assertEquals('payment_source.remove.error', $message->getHtml());
    }

    protected function tearDown()
    {
        /**
         * Remove all aci profiles
         */
        if ($this->fixtureLocator) {
            $profiles = $this->getOldProfileIds();
            if (is_array($profiles) && !empty($profiles)) {
                foreach ($profiles as $profile) {
                    if ($profile) {
                        $this->deleteAciCollectPayProfile($profile);
                    }
                }
            }
        }
    }
}
