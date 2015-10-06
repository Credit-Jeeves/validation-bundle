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
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
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
    public function editAci()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);

        /** @var Tenant $tenant */
        $tenant = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:Tenant')
            ->findOneByEmail('tenant11@example.com');

        $this->assertNotEmpty($tenant, "Check fixtures, tenant with email 'tenant11@example.com' should be exist");

        $bankPaymentAccount = $this->prepareFixturesAciCollectPay($tenant);

        $oldToken = $bankPaymentAccount->getToken();

        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('rent.sources');

        $this->session->wait($this->timeout, "jQuery('#payment-account-table').length");

        $this->assertNotNull(
            $this->page->find(
                'css',
                sprintf('#payment-account-table td:contains("%s")', $bankPaymentAccount->getName())
            ),
            sprintf('Payment account "%s" should be show', $bankPaymentAccount->getName())
        );

        $this->assertNotNull($row = $this->page->find('css', '#payment-account-row-1'));

        $row->clickLink('edit');

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymentaccounttype_name:visible').length" .
            " && jQuery('.overlay-trigger').length <= 0"
        );

        $this->assertNotNull(
            $choices = $this->page->findAll(
                'css',
                '#rentjeeves_checkoutbundle_paymentaccounttype_type_box i'
            )
        );
        $this->assertCount(2, $choices);
        $choices[1]->click();

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentaccounttype');

        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'New Card',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardAccountName' => 'Timothy Applegate',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardNumber' => '5473500000000014',
                'rentjeeves_checkoutbundle_paymentaccounttype_VerificationCode' => '902',
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationMonth' => date('n'),
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationYear' => date('Y') + 1,
            ]
        );

        $this->assertNotNull(
            $choices = $this->page->findAll(
                'css',
                '#rentjeeves_checkoutbundle_paymentaccounttype_address_choice_box i'
            )
        );
        $this->assertCount(2, $choices);
        $choices[1]->click();

        $this->page->pressButton('payment_account.edit.save');

        $this->session->wait(
            $this->timeout + 15000,
            "jQuery.trim(jQuery('#payment-account-row-1 td:first').text()) == 'New Card'"
        );

        $this->assertNotEmpty($cols = $this->page->findAll('css', '#payment-account-row-1 td'));
        $this->assertEquals('New Card', $cols[0]->getText());

        $this->getEntityManager()->refresh($bankPaymentAccount);

        $this->assertNotEquals(
            $oldToken,
            $bankPaymentAccount->getToken(),
            'Aci funding_account_id should be refreshed.'
        );

        $this->logout();
    }

    /**
     * @test
     */
    public function delAci()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);

        /** @var Tenant $tenant */
        $tenant = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:Tenant')
            ->findOneByEmail('tenant11@example.com');

        $this->assertNotEmpty($tenant, 'Check fixtures, tenant with email "tenant11@example.com" should be exist');

        $createdPaymentAccount = $this->prepareFixturesAciCollectPay($tenant);

        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('rent.sources');

        $this->session->wait($this->timeout, "jQuery('#payment-account-table').length");
        $this->assertNotEmpty($rows = $this->page->findAll('css', '#payment-account-table tbody tr'));
        $rows[0]->clickLink('delete'); // remove last account
        $this->session->wait($this->timeout, "jQuery('#payment-account-delete:visible').length");
        $this->page->clickLink('payment_account.delete.yes');

        $this->session->wait(
            $this->timeout,
            "jQuery('#payment-account-table').length"
        );
        $this->assertEmpty($rows = $this->page->findAll('css', '#payment-account-table tbody tr'));

        $this->getEntityManager()->refresh($tenant);

        $this->assertCount(
            0,
            $tenant->getPaymentAccounts()->filter(
                function (PaymentAccount $paymentAccount) use ($createdPaymentAccount) {
                    return $createdPaymentAccount->getId() === $paymentAccount->getId();
                }
            ),
            'Aci payment account should be removed.'
        );
        $this->logout();
    }

    /**
     * @test
     */
    public function editHeartland()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $tenant = $this->getEntityManager()->getRepository('RjDataBundle:Tenant')
            ->findOneByEmail('tenant11@example.com');
        $this->assertNotEmpty($tenant);

        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('rent.sources');

        $this->session->wait($this->timeout, "jQuery('#payment-account-table').length");
        $this->assertNotNull($row = $this->page->find('css', '#payment-account-row-3'));
        $row->clickLink('edit');

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymentaccounttype_name:visible').length" .
            " && jQuery('.overlay-trigger').length <= 0"
        );

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentaccounttype');
        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymentaccounttype_type_1' => true
            ]
        );

        $this->page->pressButton('payment_account.edit.save');

        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#payment-account-edit .attention-box li').length"
        );
        $this->assertNotNull($errors = $this->page->findAll('css', '#payment-account-edit .attention-box li'));
        $this->assertCount(3, $errors);

        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'New Card',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardAccountName' => 'Timothy Applegate',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardNumber' => '5473500000000014',
                'rentjeeves_checkoutbundle_paymentaccounttype_VerificationCode' => '902',
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationMonth' => date('n'),
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationYear' => date('Y') + 1,
            ]
        );
        $this->assertNotNull(
            $choices = $this->page->findAll(
                'css',
                '#rentjeeves_checkoutbundle_paymentaccounttype_address_choice_box i'
            )
        );
        $this->assertCount(2, $choices);
        $choices[1]->click();

        $this->page->pressButton('payment_account.edit.save');

        $this->session->wait(
            $this->timeout + 15000,
            "jQuery.trim(jQuery('#payment-account-row-3 td:first').text()) == 'New Card'"
        );

        $this->assertNotNull($cols = $this->page->findAll('css', '#payment-account-row-3 td'));
        $this->assertEquals('New Card', $cols[0]->getText());

        $this->logout();
    }

    /**
     * @test
     */
    public function editTheSame()
    {
        $this->setDefaultSession('selenium2');
        $this->load(false);
        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('rent.sources');

        $this->session->wait($this->timeout, "jQuery('#payment-account-row-2').length");
        $this->assertNotNull($row = $this->page->find('css', '#payment-account-row-2'));
        $row->clickLink('edit');

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymentaccounttype_name:visible').length" .
            " && jQuery('.overlay-trigger').length <= 0"
        );

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentaccounttype');
        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymentaccounttype_type_1' => true,
                'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'Edited',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardAccountName' => 'Timothy Applegate',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardNumber' => '5473500000000014',
                'rentjeeves_checkoutbundle_paymentaccounttype_VerificationCode' => '123',
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationMonth' => date('n'),
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationYear' => date('Y') + 1,
            ]
        );
        $this->assertNotNull(
            $choices = $this->page->findAll(
                'css',
                '#rentjeeves_checkoutbundle_paymentaccounttype_address_choice_box i'
            )
        );
        $this->assertCount(2, $choices);
        $choices[1]->click();

        $this->page->pressButton('payment_account.edit.save');

        $this->session->wait(
            $this->timeout,
            "jQuery.trim(jQuery('#payment-account-row-2 td:first').text()) == 'Edited'"
        );

        $this->assertNotNull($cols = $this->page->findAll('css', '#payment-account-row-2 td'));
        $this->assertEquals('Edited', $cols[0]->getText());
    }

    /**
     * @test
     */
    public function delHeartland()
    {
        $this->setDefaultSession('selenium2');
        $this->load(false);
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
     * @test
     * Active contracts mean not finished and not deleted contracts
     */
    public function shouldShowJustActivePaymentSources()
    {
        $this->setDefaultSession('goutte');
        $this->load(true);
        /** @var Tenant $tenant */
        $tenant = $this->getEntityManager()
            ->getRepository('RjDataBundle:Tenant')
            ->findOneBy(['email' => 'tenant11@example.com']);
        $this->assertNotEmpty($tenant);
        $this->getEntityManager()->getConnection()->exec(
            sprintf(
                'UPDATE rj_contract SET status = "%s" WHERE tenant_id = %d',
                ContractStatus::DELETED,
                $tenant->getId()
            )
        );
        $paymentAccounts = $tenant->getPaymentAccounts();
        $this->assertCount(3, $paymentAccounts, 'Please check fixtures, tenant should have 3 payment accounts');

        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('rent.sources');

        $this->assertNotNull($rows = $this->page->findAll('css', '.properties-table tbody tr'));
        $this->assertCount(
            0,
            $rows,
            'Should display no payment accounts b/c tenant doesn\'t have any active contracts'
        );
        /** @var Contract $contract */
        $contract = $tenant->getContracts()->first();
        /** @var Contract $contract2 */
        $contract2 = $tenant->getContracts()->next();
        $contract->setStatus(ContractStatus::APPROVED);
        $contract->getGroup()->getGroupSettings()->setPaymentProcessor(PaymentProcessor::HEARTLAND);
        $this->getEntityManager()->persist($contract);
        $this->getEntityManager()->flush();

        $this->session->reload();

        $this->assertNotNull($rows = $this->page->findAll('css', '.properties-table tbody tr'));
        $this->assertCount(
            3,
            $rows,
            'Should display 3 payment accounts b/c tenant has 3 heartland payment account like active contracts group'
        );

        $contract->getGroup()->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);
        $this->getEntityManager()->flush();

        $this->session->reload();

        $this->assertNotNull($rows = $this->page->findAll('css', '.properties-table tbody tr'));
        $this->assertCount(
            0,
            $rows,
            'Should display no payment accounts b/c tenant has 3 heartland payment account' .
            ' but active contract group has aci payment processor'
        );

        $contract->getGroup()->getGroupSettings()->setPaymentProcessor(PaymentProcessor::HEARTLAND);
        /** @var PaymentAccount $paymentAccount */
        $paymentAccount = $paymentAccounts->first();
        $paymentAccount->setPaymentProcessor(PaymentProcessor::ACI);
        $this->getEntityManager()->persist($paymentAccount);
        $this->getEntityManager()->flush();

        $this->session->reload();

        $this->assertNotNull($rows = $this->page->findAll('css', '.properties-table tbody tr'));
        $this->assertCount(
            2,
            $rows,
            'Should display 2 payment accounts b/c tenant has 2 heartland payment account like active contracts group' .
            ' and shouldn\'t display 1 aci payment account'
        );

        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find(25);
        $contract2->setGroup($group);
        $contract2->setStatus(ContractStatus::PENDING);
        $group->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);
        $this->getEntityManager()->persist($contract2);
        $this->getEntityManager()->persist($group);
        $this->getEntityManager()->flush();

        $this->session->reload();

        $this->assertNotNull($rows = $this->page->findAll('css', '.properties-table tbody tr'));
        $this->assertCount(
            3,
            $rows,
            'Should display 3 payment accounts b/c tenant has 2 heartland payment account like active contracts group' .
            ' and should display 1 aci payment account like another active contract'
        );
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
