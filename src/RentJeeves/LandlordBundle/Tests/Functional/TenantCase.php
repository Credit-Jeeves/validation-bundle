<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class TenantCase extends BaseTestCase
{
    protected $timeout = 30000;
    /**
     * @test
     */
    public function approve()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        
        $this->assertNotNull($contractPendings = $this->page->findAll('css', '.contract-pending'));
        $this->assertCount(2, $contractPendings, 'Wrong number of pending');
        $this->assertNotNull($approve = $this->page->find('css', '.approve'));
        $approve->click();
        $this->page->pressButton('approve.tenant');
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($propertiesTable = $this->page->find('css', '.properties-table'));
        $this->assertNotNull($contractPendings = $this->page->findAll('css', '.contract-pending'));
        $this->assertCount(1, $contractPendings, 'Wrong number of pending');
        $this->logout();
    }

    /**
     * @test
     */
    public function sort()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($td = $this->page->findAll('css', 'td'));
        $this->assertEquals('Timothy Applegate', $td[1]->getText(), 'Wrong text in field');

        $this->assertNotNull($tenant = $this->page->find('css', '#first_name'));
        $tenant->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($td = $this->page->findAll('css', 'td'));
        $this->assertEquals('William Johnson', $td[1]->getText(), 'Wrong text in field');

        $this->assertNotNull($tenant = $this->page->find('css', '#first_name'));
        $tenant->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($td = $this->page->findAll('css', 'td'));
        $this->assertEquals('Alex Jordan', $td[1]->getText(), 'Wrong text in field');
        $this->logout();
    }

    /**
     * @test
     */
    public function edit()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($approve = $this->page->find('css', '.approve'));
        $approve->click();
        $this->page->pressButton('edit.Info');
        $this->session->evaluateScript(
            "$('.half-of-right').val(' ');"
        );

        $this->assertNotNull($amount = $this->page->find('css', '.half-of-right'));
        $amount->setValue('200');
        $this->assertNotNull($contractEditStart = $this->page->find('css', '#contractEditStart'));
        $contractEditStart->setValue('08/01/2013');
        
        $this->assertNotNull($contractEditStart = $this->page->find('css', '#contractEditFinish'));
        $contractEditStart->setValue('08/25/2013');
        
        $this->page->pressButton('savechanges');
        
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($approve = $this->page->find('css', '.approve'));
        $approve->click();

        $this->assertNotNull($editStart = $this->page->find('css', '#contract-edit-start'));
        $this->assertNotNull($editFinish = $this->page->find('css', '#contract-edit-finish'));
        $this->assertNotNull($amount = $this->page->find('css', '#amount-edit'));

        $this->assertEquals('08/01/2013', $editStart->getText(), 'Wrong edit start');
        $this->assertEquals('08/25/2013', $editFinish->getText(), 'Wrong edit finish');
        $this->assertEquals('$200', $amount->getText(), 'Wrong edit amount');
        $this->logout();
    }

    /**
     * @test
     */
    public function remove()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (13)', $allh2->getText(), 'Wrong count of tenants');
        $this->assertNotNull($approve = $this->page->find('css', '.approve'));
        $approve->click();
        $this->page->pressButton('edit.Info');
        $this->page->clickLink('remove.tenant');
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (12)', $allh2->getText(), 'Wrong count');
        $this->logout();
    }

    /**
     * @test
     */
    public function search()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (13)', $allh2->getText(), 'Wrong count');
        $this->assertNotNull($searchField = $this->page->find('css', '#searsh-field'));
        $searchField->setValue('PENDING');
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (2)', $allh2->getText(), 'Wrong count');
        $this->logout();
    }

    /**
     * @test
     */
    public function addTenantNoneExist()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->clearEmail();
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (13)', $allh2->getText(), 'Wrong count');
        $this->page->pressButton('add.tenant');
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype'));
        $this->page->pressButton('invite.tenant');
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(4, $errorList, 'Wrong number of errors');
        $this->fillForm(
            $form,
            array(
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_first_name' => 'Alex',
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_last_name'  => 'Sharamko',
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_phone'      => '12345',
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_email'      => 'test@email.ru',
                'rentjeeves_landlordbundle_invitetenantcontracttype_contract_rent'     => '200',
                'rentjeeves_landlordbundle_invitetenantcontracttype_contract_startAt'  => '01/08/2013',
                'rentjeeves_landlordbundle_invitetenantcontracttype_contract_finishAt' => '01/12/2013',
            )
        );
        $this->page->pressButton('invite.tenant');

        //Check created contracts
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (14)', $allh2->getText(), 'Wrong count');
        $this->assertNotNull($searchField = $this->page->find('css', '#searsh-field'));
        $searchField->setValue('INVITE');
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (1)', $allh2->getText(), 'Wrong count');
        $this->logout();
        // end

        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(1, $email, 'Wrong number of emails');
        $email = array_pop($email);
        $email->click();
        $this->page->clickLink('text/html');
        $this->assertNotNull($link = $this->page->find('css', '#payRentLink'));
        $link->click();
        $this->assertNotNull($form = $this->page->find('css', '#tenantInviteRegister'));
        $form->pressButton('continue');
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(2, $errorList, 'Wrong number of pending');
        $this->fillForm(
            $form,
            array(
                'rentjeeves_publicbundle_tenanttype_password_Password'          => 'pass',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password'   => 'pass',
                'rentjeeves_publicbundle_tenanttype_tos'                        => true,
            )
        );
        $form->pressButton('continue');
        $this->assertNotNull($contracts = $this->page->findAll('css', '.listOfPaymentsActive>tbody>tr'));
        $this->assertCount(1, $contracts, 'wrong number of contracts');
    }

    /**
     * @test
     */
    public function addTenantExist()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->clearEmail();
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (13)', $allh2->getText(), 'Wrong count');
        $this->page->pressButton('add.tenant');
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype'));
        $this->page->pressButton('invite.tenant');
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(4, $errorList, 'Wrong number of errors');
        $this->fillForm(
            $form,
            array(
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_first_name' => 'Alex',
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_last_name'  => 'Sharamko',
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_phone'      => '12345',
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_email'      => 'robyn@rentrack.com',
                'rentjeeves_landlordbundle_invitetenantcontracttype_contract_rent'     => '200',
                'rentjeeves_landlordbundle_invitetenantcontracttype_contract_startAt'  => '01/08/2013',
                'rentjeeves_landlordbundle_invitetenantcontracttype_contract_finishAt' => '01/12/2013',
            )
        );
        $this->session->wait($this->timeout, "$('#userExistMessage').is(':visible')");
        $this->page->pressButton('invite.tenant');

        //Check created contracts
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (14)', $allh2->getText(), 'Wrong count');
        $this->assertNotNull($searchField = $this->page->find('css', '#searsh-field'));
        $searchField->setValue('ACTIVE');
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (1)', $allh2->getText(), 'Wrong count');
        $this->logout();
        // end

        $this->setDefaultSession('goutte');
        $this->login('robyn@rentrack.com', 'pass');
        $this->assertNotNull($contracts = $this->page->findAll('css', '.listOfPaymentsActive>tbody>tr'));
        $this->assertCount(1, $contracts, 'wrong number of contracts');
        $this->logout();
    }
}
