<?php
namespace RentJeeves\TenantBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class SourcesCase extends BaseTestCase
{

    public function providerForEdit()
    {
        return [
            [PaymentProcessor::ACI],
            [PaymentProcessor::HEARTLAND],
        ];
    }

    /**
     * @test
     * @dataProvider providerForEdit
     */
    public function edit($paymentProcessor)
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $tenant = $this->getEntityManager()->getRepository('RjDataBundle:Tenant')
            ->findOneByEmail('tenant11@example.com');
        $this->assertNotEmpty($tenant);
        $paymentAccount = $this->getEntityManager()->getRepository('RjDataBundle:PaymentAccount')->findOneBy(
            [
                'name' => 'RT Card',
                'user' => $tenant
            ]
        );
        $depositAccounts = $paymentAccount->getDepositAccounts();
        /** @var DepositAccount $depositAccount */
        foreach ($depositAccounts as $depositAccount) {
            $depositAccount->setPaymentProcessor($paymentProcessor);
        }
        $this->getEntityManager()->flush();
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
            array(
                'rentjeeves_checkoutbundle_paymentaccounttype_type_1' => true
            )
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
            array(
                'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'New Card',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardAccountName' => 'Timothy Applegate',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardNumber' => '5473500000000014',
                'rentjeeves_checkoutbundle_paymentaccounttype_VerificationCode' => '902',
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationMonth' => date('n'),
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationYear' => date('Y') + 1,
//                'rentjeeves_checkoutbundle_paymentaccounttype_address_choice_24' => true,
            )
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
            array(
                'rentjeeves_checkoutbundle_paymentaccounttype_type_1' => true,
                'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'Edited',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardAccountName' => 'Timothy Applegate',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardNumber' => '5473500000000014',
                'rentjeeves_checkoutbundle_paymentaccounttype_VerificationCode' => '123',
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationMonth' => date('n'),
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationYear' => date('Y') + 1,
//                'rentjeeves_checkoutbundle_paymentaccounttype_address_choice_24' => true,
            )
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
    public function del()
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
}
