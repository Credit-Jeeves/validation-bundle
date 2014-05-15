<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use CreditJeeves\DataBundle\Model\User;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Model\Contract;
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
        $this->assertNotNull($contractPendings = $this->page->findAll('css', '.contract-pending>div'));
        $this->assertCount(3, $contractPendings, 'Wrong number of pending');
        $this->assertEquals('PENDING', $contractPendings[0]->getHtml());
        $this->assertEquals('PENDING', $contractPendings[1]->getHtml());
        $this->assertEquals('CONTRACT ENDED', $contractPendings[2]->getHtml());
        $this->assertNotNull($approve = $this->page->find('css', '.approve'));
        $approve->click();
        $this->page->pressButton('approve.tenant');
        $this->session->wait($this->timeout, "$('div.attention-box').is(':visible')");
        $this->assertNotNull($errors = $this->page->findAll('css', 'div.attention-box ul.default li'));
        $this->assertCount(1, $errors, 'Wrong number of errors');
        $this->assertNotNull($amount = $this->page->find('css', '#amount-approve'));
        $amount->setValue('200');
        $this->assertNotNull($start = $this->page->find('css', '#contractApproveStart'));
        $start->click();
        $this->session->wait($this->timeout, "$('#ui-datepicker-div .ui-datepicker-today').is(':visible')");
        $this->assertNotNull($today = $this->page->find('css', '#ui-datepicker-div .ui-datepicker-today'));
        $today->click();
        $this->session->wait($this->timeout, "!$('#ui-datepicker-div').is(':visible')");
        $this->page->pressButton('approve.tenant');
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($propertiesTable = $this->page->find('css', '.properties-table'));
        $this->assertNotNull($contractPendings = $this->page->findAll('css', '.contract-pending>div'));
        $this->assertCount(2, $contractPendings, 'Wrong number of pending');
        $this->assertEquals('PENDING', $contractPendings[0]->getHtml());
        $this->assertEquals('CONTRACT ENDED', $contractPendings[1]->getHtml());
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
        $this->assertEquals('Connie Webster', $td[1]->getText(), 'Wrong text in field');

        $this->assertNotNull($tenant = $this->page->find('css', '#first_name'));
        $tenant->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($td = $this->page->findAll('css', 'td'));
        $this->assertEquals('Timothy Applegate', $td[1]->getText(), 'Wrong text in field');
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

        $this->session->wait($this->timeout, "$('#tenant-edit-property-popup .loader').is(':visible')");
        $this->session->wait($this->timeout, "!$('#tenant-edit-property-popup .loader').is(':visible')");

        $this->assertNotNull($amount = $this->page->find('css', '#amount-edit'));
        $amount->setValue('200');

        $start = $this->page->find('css', '#contractEditStart');
        $this->assertNotNull($start);
        $start->click();

        $today = $this->page->find('css', '#ui-datepicker-div .ui-datepicker-today');
        $this->assertNotNull($today);
        $today->click();
        $this->session->wait($this->timeout, "!$('#ui-datepicker-div').is(':visible')");

        $finish = $this->page->find('css', '#contractEditFinish');
        $this->assertNotNull($finish);
        $finish->click();
        $this->session->wait($this->timeout, "$('#ui-datepicker-div').is(':visible')");

        $next = $this->page->find('css', '#ui-datepicker-div .ui-datepicker-next');
        $this->assertNotNull($next);
        $next->click();

        $future = $this->page->findAll('css', '#ui-datepicker-div .ui-state-default');
        $this->assertNotNull($future);
        $future[count($future)-1]->click();

        $this->assertNotNull($contractEditStart = $this->page->find('css', '#contractEditStart'));
        $start = $contractEditStart->getValue();

        $this->assertNotNull($contractEditStart = $this->page->find('css', '#contractEditFinish'));
        $finish = $contractEditStart->getValue();

        $this->assertNotNull($unitEdit = $this->page->find('css', '#unit-edit'));
        $unitEdit->selectOption('2-e'); //

        $this->page->pressButton('savechanges');
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($approve = $this->page->find('css', '.approve'));
        $approve->click();

        $this->assertNotNull($editStart = $this->page->find('css', '#contractApproveStart'));
        $this->assertNotNull($editFinish = $this->page->find('css', '#contractApproveFinish'));
        $this->assertNotNull($amount = $this->page->find('css', '#amount-approve'));
        $this->assertNotNull($address = $this->page->find('css', '#tenant-approve-property-popup .addressDiv'));
        $this->assertEquals($start, $editStart->getValue(), 'Wrong edit start');
        $this->assertEquals($finish, $editFinish->getValue(), 'Wrong edit finish');
        $this->assertEquals('200', $amount->getValue(), 'Wrong edit amount');
        $this->assertEquals('770 Broadway, Manhattan #2-e', $address->getHtml(), 'Wrong edit unit');
        $this->logout();
    }

    /**
     * @test
     */
    public function remove()
    {
        $this->clearEmail();
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (16)', $allh2->getText(), 'Wrong count of tenants');
        $this->assertNotNull($approve = $this->page->find('css', '.approve'));
        $approve->click();
        $this->page->pressButton('edit.Info');
        $this->page->clickLink('remove.tenant');
        $this->page->pressButton('yes.remove.contract');
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (15)', $allh2->getText(), 'Wrong count');
        $this->logout();
        //Check email notify tenant about removed contract by landlord
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(1, $email, 'Wrong number of emails');
    }

    /**
     * @test
     */
    public function endContract()
    {
        $this->clearEmail();
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (16)', $allh2->getText(), 'Wrong count of tenants');

        $this->assertNotNull($searchField = $this->page->find('css', '#searchPaymentsStatus_link'));
        $searchField->click();
        $this->assertNotNull($current = $this->page->find('css', '#searchPaymentsStatus_li_4'));
        $current->click();
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments-status'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (7)', $allh2->getText(), 'Wrong count of tenants');
        $this->assertNotNull($approve = $this->page->find('css', '.edit'));
        $approve->click();
        $this->page->clickLink('end.br.contract');
        $this->assertNotNull($outstandingBalance = $this->page->find('css', '.outstandingBalance'));
        $outstandingBalance->setValue(223.21);
        $this->page->pressButton('yes.end.contract');
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (6)', $allh2->getText(), 'Wrong count');
        $this->logout();
        //Check email notify tenant about removed contract by landlord
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(1, $email, 'Wrong number of emails');

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $contracts = $em->getRepository('RjDataBundle:Contract')->findBy(
            array(
                'uncollectedBalance' => 223.21,
                'status'             => 'finished',
            )
        );
        $this->assertCount(1, $contracts, 'Wrong count contract');
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
        $this->assertEquals('All (16)', $allh2->getText(), 'Wrong count');
        $this->assertNotNull($searchField = $this->page->find('css', '#searchPaymentsStatus_link'));
        $searchField->setValue('contract.status.pending');
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments-status'));
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
        $this->assertEquals('All (16)', $allh2->getText(), 'Wrong count');
        $this->page->pressButton('add.tenant');
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype'));
        $this->page->pressButton('invite.tenant');
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(3, $errorList, 'Wrong number of errors');
        $this->fillForm(
            $form,
            array(
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_first_name' => 'Alex',
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_last_name'  => 'Sharamko',
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_phone'      => '12345',
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_email'      => 'test@email.ru',
                'rentjeeves_landlordbundle_invitetenantcontracttype_contract_rent'     => '200',
                'rentjeeves_landlordbundle_invitetenantcontracttype_contract_finishAtType_1' => true,
                'rentjeeves_landlordbundle_invitetenantcontracttype_contract_dueDate'   => 23,
            )
        );
        $start = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype_contract_startAt');
        $this->assertNotNull($start);
        $start->click();

        $today = $this->page->find('css', '#ui-datepicker-div .ui-datepicker-today');
        $this->assertNotNull($today);
        $today->click();
        $this->session->wait($this->timeout, "!$('#ui-datepicker-div').is(':visible')");

        $finish = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype_contract_finishAt');
        $this->assertNotNull($finish);
        $finish->click();
        $this->session->wait($this->timeout, "$('#ui-datepicker-div').is(':visible')");

        $next = $this->page->find('css', '#ui-datepicker-div .ui-datepicker-next');
        $this->assertNotNull($next);
        $next->click();

        $future = $this->page->findAll('css', '#ui-datepicker-div .ui-state-default');
        $this->assertNotNull($future);
        $future[count($future)-1]->click();

        $this->page->pressButton('invite.tenant');

        //Check created contracts
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (17)', $allh2->getText(), 'Wrong count');
        $this->assertNotNull($searchField = $this->page->find('css', '#searchPaymentsStatus_link'));
        $searchField->setValue('contract.status.invite');
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments-status'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (2)', $allh2->getText(), 'Wrong count');
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
        //$this->session->wait($this->timeout, "$('#rentjeeves_publicbundle_tenanttype').is(':visible')");
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_publicbundle_tenanttype'));
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
        $this->assertNotNull($contracts = $this->page->findAll('css', 'div.table-margin table tbody tr'));
        $this->assertCount(2, $contracts, 'wrong number of contracts');

        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'test@email.ru',
            )
        );

        $contracts = $tenant->getContracts();
        $this->assertCount(1, $contracts, 'wrong number of contracts');
        /**
         * @var $contract Contract
         */
        $contract = $contracts->get(0);
        $this->assertEquals(23, $contract->getDueDate());
        $this->assertNotNull($contract->getFinishAt());
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
        $this->assertEquals('All (16)', $allh2->getText(), 'Wrong count');
        $this->page->pressButton('add.tenant');
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype'));
        $this->page->pressButton('invite.tenant');
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(3, $errorList, 'Wrong number of errors');
        $this->fillForm(
            $form,
            array(
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_first_name' => 'Alex',
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_last_name'  => 'Sharamko',
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_phone'      => '12345',
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_email'      => 'robyn@rentrack.com',
                'rentjeeves_landlordbundle_invitetenantcontracttype_contract_rent'     => '200',
                'rentjeeves_landlordbundle_invitetenantcontracttype_contract_dueDate'   => 13,
            )
        );

        $start = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype_contract_startAt');
        $this->assertNotNull($start);
        $start->click();

        $today = $this->page->find('css', '#ui-datepicker-div .ui-datepicker-today');
        $this->assertNotNull($today);
        $today->click();
        $this->session->wait($this->timeout, "!$('#ui-datepicker-div').is(':visible')");

        $finish = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype_contract_finishAt');
        $this->assertNotNull($finish);
        $finish->click();
        $this->session->wait($this->timeout, "$('#ui-datepicker-div').is(':visible')");

        $next = $this->page->find('css', '#ui-datepicker-div .ui-datepicker-next');
        $this->assertNotNull($next);
        $next->click();

        $future = $this->page->findAll('css', '#ui-datepicker-div .ui-state-default');
        $this->assertNotNull($future);
        $future[count($future)-1]->click();

        $this->fillForm(
            $form,
            array(
                'rentjeeves_landlordbundle_invitetenantcontracttype_contract_finishAtType_0' => true,
            )
        );

        $this->session->wait($this->timeout, "$('#userExistMessage').is(':visible')");
        $this->page->pressButton('invite.tenant');

        //Check created contracts
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (17)', $allh2->getText(), 'Wrong count');
        $this->assertNotNull($searchField = $this->page->find('css', '#searchPaymentsStatus_link'));
        $searchField->setValue('contract.status.approved');
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments-status'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (2)', $allh2->getText(), 'Wrong count');
        $this->logout();
        // end

        $this->setDefaultSession('goutte');
        $this->login('robyn@rentrack.com', 'pass');
        $this->assertNotNull($contracts = $this->page->findAll('css', 'div.table-margin table tbody tr'));
        $this->assertCount(2, $contracts, 'wrong number of contracts');
        $this->logout();

        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'robyn@rentrack.com',
            )
        );

        $contracts = $tenant->getContracts();
        $this->assertCount(1, $contracts, 'wrong number of contracts');
        /**
         * @var $contract Contract
         */
        $contract = $contracts->get(0);
        $this->assertEquals(13, $contract->getDueDate());
        $this->assertNull($contract->getFinishAt());
    }

    /**
     * @test
     */
    public function checkNotifyLandlord()
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
        $this->assertEquals('All (16)', $allh2->getText(), 'Wrong count');
        $this->page->pressButton('add.tenant');
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype'));
        $this->page->pressButton('invite.tenant');
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(3, $errorList, 'Wrong number of errors');
        $this->fillForm(
            $form,
            array(
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_email'      => 'landlord1@example.com'
            )
        );
        $this->session->wait($this->timeout, "$('#userExistMessageLanlord').is(':visible')");
        $this->logout();
    }

    private function sendReminder($nCountEmails = 1)
    {
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "typeof $ != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        //Check created contracts
        $this->assertNotNull($search = $this->page->find('css', '#searchPaymentsStatus_link'));
        $search->click();
        $this->assertNotNull($inviteStatus = $this->page->find('css', '#searchPaymentsStatus_li_2'));
        $inviteStatus->click();

        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments-status'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");

        $this->assertNotNull($all = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (1)', $all->getText(), 'Wrong count');

        $this->assertNotNull($review = $this->page->find('css', 'a.edit'));
        $review->click();
        $this->assertNotNull($sendReminder = $this->page->find('css', '.sendReminder'));
        $this->session->wait($this->timeout, "$('.loader').is(':visible')");
        $this->session->wait($this->timeout, "!$('.loader').is(':visible')");
        $sendReminder->click();
        $this->session->wait($this->timeout, "$('.overlay-trigger').is(':visible')");
        $this->session->wait($this->timeout, "!$('.overlay-trigger').is(':visible')");
        $sendReminder->click();

        $this->session->wait($this->timeout, "$('.overlay-trigger').is(':visible')");
        $this->session->wait($this->timeout, "!$('.overlay-trigger')");

        $this->assertNotNull($error = $this->page->find('css', '.default>li'));
        $this->assertEquals('contract.reminder.error.already.send', $error->getText(), 'Wrong text error');
        $this->logout();
        // check email
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount($nCountEmails, $email, 'Wrong number of emails');
        // end
    }

    /**
     * @test
     */
    public function checkReminder()
    {
        $this->clearEmail();
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (16)', $allh2->getText(), 'Wrong count');
        $this->page->pressButton('add.tenant');
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype'));
        $this->page->pressButton('invite.tenant');
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(3, $errorList, 'Wrong number of errors');
        $this->fillForm(
            $form,
            array(
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_first_name' => 'Alex',
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_last_name'  => 'Sharamko',
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_phone'      => '12345',
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_email'      => 'test123@email.ru',
                'rentjeeves_landlordbundle_invitetenantcontracttype_contract_rent'     => '200',
            )
        );
        $start = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype_contract_startAt');
        $this->assertNotNull($start);
        $start->click();

        $today = $this->page->find('css', '#ui-datepicker-div .ui-datepicker-today');
        $this->assertNotNull($today);
        $today->click();
        $this->session->wait($this->timeout, "!$('#ui-datepicker-div').is(':visible')");

        $finish = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype_contract_finishAt');
        $this->assertNotNull($finish);
        $finish->click();
        $this->session->wait($this->timeout, "$('#ui-datepicker-div').is(':visible')");

        $next = $this->page->find('css', '#ui-datepicker-div .ui-datepicker-next');
        $this->assertNotNull($next);
        $next->click();

        $future = $this->page->findAll('css', '#ui-datepicker-div .ui-state-default');
        $this->assertNotNull($future);
        $future[count($future)-1]->click();

        $this->page->pressButton('invite.tenant');
        $this->logout();

        $this->clearEmail();
        $this->sendReminder();
        $this->clearEmail();

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        /** @var $user User */
        $user = $em->getRepository('DataBundle:User')->findOneBy(array('email' => 'test123@email.ru'));
        if (empty($user)) {
            $this->assertFalse(true, 'User does not exist');
        }

        $user->setIsActive(true);
        $em->persist($user);
        $em->flush();
        $this->sendReminder(0);
    }

    /**
     * @test
     * @depends checkReminder
     */
    public function revoke()
    {
        $this->clearEmail();
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        //Check created contracts
        $this->assertNotNull($search = $this->page->find('css', '#searchPaymentsStatus_link'));
        $search->click();
        $this->assertNotNull($inviteStatus = $this->page->find('css', '#searchPaymentsStatus_li_2'));
        $inviteStatus->click();
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments-status'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");

        $this->assertNotNull($all = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (1)', $all->getText(), 'Wrong count');

        $this->assertNotNull($review = $this->page->find('css', 'a.edit'));
        $review->click();
        
        $this->page->clickLink('revoke.inv');
        $this->page->pressButton('yes.revoke.inv');

        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        //Check created contracts
        $this->assertNotNull($search = $this->page->find('css', '#searchPaymentsStatus_link'));
        $search->click();
        $this->assertNotNull($inviteStatus = $this->page->find('css', '#searchPaymentsStatus_li_2'));
        $inviteStatus->click();
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments-status'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");

        $this->assertNotNull($all = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (0)', $all->getText(), 'Wrong count');
        $this->logout();
        //Check email notify tenant about removed contract by landlord
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(1, $email, 'Wrong number of emails');
    }
}
