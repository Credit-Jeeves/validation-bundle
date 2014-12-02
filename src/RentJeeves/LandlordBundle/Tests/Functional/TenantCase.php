<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Model\User;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;
use DateTime;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class TenantCase extends BaseTestCase
{
    protected $timeout = 35000;

    const ALL = 'All (18)';
    const ALL_PLUS_ONE = 'All (19)';
    const ALL_MINUS_ONE = 'All (17)';

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
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($contractPendings = $this->page->findAll('css', '.contract-pending>div'));
        $this->assertCount(3, $contractPendings, 'Wrong number of pending');
        $this->assertEquals('contract.statuses.pending', $contractPendings[0]->getHtml());
        $this->assertEquals('contract.statuses.pending', $contractPendings[1]->getHtml());
        $this->assertEquals('contract.statuses.contract_ended', $contractPendings[2]->getHtml());
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
        $this->session->wait($this->timeout, "!$('#tenant-approve-property-popup').is(':visible')");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($propertiesTable = $this->page->find('css', '.properties-table'));
        $this->assertNotNull($contractPendings = $this->page->findAll('css', '.contract-pending>div'));
        $this->assertCount(2, $contractPendings, 'Wrong number of pending');
        $this->assertEquals('contract.statuses.pending', $contractPendings[0]->getHtml());
        $this->assertEquals('contract.statuses.contract_ended', $contractPendings[1]->getHtml());
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
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($td = $this->page->findAll('css', 'td'));
        $this->assertEquals('Timothy Applegate', $td[1]->getText(), 'Wrong text in field');

        $this->assertNotNull($tenant = $this->page->find('css', '#first_name'));
        $tenant->click();
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($td = $this->page->findAll('css', 'td'));
        $this->assertEquals('William Johnson', $td[1]->getText(), 'Wrong text in field');

        $this->assertNotNull($tenant = $this->page->find('css', '#first_name'));
        $tenant->click();
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($td = $this->page->findAll('css', 'td'));
        $this->assertEquals('Alex Jordan', $td[1]->getText(), 'Wrong text in field');
        $this->logout();
    }

    public function providerEdit()
    {
        return array(
            array($isIntegrated = false),
            array($isIntegrated = true),
        );
    }

    /**
     * @test
     * @dataProvider providerEdit
     */
    public function edit($isIntegrated)
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /**
         * @var $group Group
         */
        $group = $em->getRepository('DataBundle:Group')->findOneByName('Sea side Rent Group');
        $setting = $group->getGroupSettings();
        $setting->setIsIntegrated($isIntegrated);
        $em->persist($setting);
        $em->flush();

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");

        $this->assertNotNull($select = $this->page->find('css', '.group-select>a'));
        $select->click();

        $this->assertNotNull($selectOption = $this->page->find('css', '#holding-group_li_1>span'));
        $selectOption->click();
        $this->session->wait(2000, "false"); // wait refresh page

        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($approve = $this->page->find('css', '.approve'));
        $approve->click();

        $this->session->wait($this->timeout, "$('#tenant-approve-property-popup').is(':visible')");

        $this->page->pressButton('edit.Info');
        $this->session->wait($this->timeout, "$('#unit-edit').val() > 0");
        $this->session->evaluateScript(
            "$('#amount-edit').val(' ');"
        );

        $this->assertNotNull($amount = $this->page->find('css', '#amount-edit'));
        $amount->setValue('7677.00');
        if ($isIntegrated) {
            $this->assertNotNull($resident = $this->page->find('css', '#resident-edit'));
            $resident->setValue('t12345');
        }

        $this->session->evaluateScript(
            "$('#contractEditStart').focus()"
        );

        // click next_payment_date and select today
        $start = $this->page->find('css', '#contractEditStart');
        $this->assertNotNull($start);
        $start->click();

        $this->session->wait($this->timeout, "$('#ui-datepicker-div').is(':visible')");

        $today = $this->page->find('css', '#ui-datepicker-div .ui-datepicker-today');
        $this->assertNotNull($today);
        $today->click();
        $this->session->wait($this->timeout, "!$('#ui-datepicker-div').is(':visible')");
        // end selected next_payment_date

        $this->assertNotNull($amount = $this->page->find('css', '#amount-edit'));
        $amount->setValue(7677.00);
        // choose input radio ON, and select date finish

        $endAtRadio = $this->page->find('css', '#tenant-edit-property-popup .finishAtLabel');
        $this->assertNotNull($endAtRadio);
        $endAtRadio->click();

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
        // end select finish date

        $this->assertNotNull($contractEditStart = $this->page->find('css', '#contractEditStart'));
        $start = $contractEditStart->getValue();

        $this->assertNotNull($contractEditStart = $this->page->find('css', '#contractEditFinish'));
        $finish = $contractEditStart->getValue();

        $this->assertNotNull($unitEdit = $this->page->find('css', '#unit-edit'));
        $unitEdit->selectOption('2-e'); //

        $this->assertNotNull($unitEdit = $this->page->find('css', '.dueDateEdit'));
        $unitEdit->selectOption('14'); //

        if ($isIntegrated) {
            $this->assertNotNull($balanceField = $this->page->find('css', '.balance-field'));
            $balanceField->setValue("200.00");
        }

        $this->page->pressButton('savechanges');
        $this->session->wait($this->timeout, "!$('#tenant-edit-property-popup').is(':visible')");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($approve = $this->page->find('css', '.approve'));
        $approve->click();

        $this->assertNotNull($editStart = $this->page->find('css', '#contractApproveStart'));
        $this->assertNotNull($editFinish = $this->page->find('css', '#contractApproveFinish'));
        $this->assertNotNull($amount = $this->page->find('css', '#amount-approve'));
        $this->assertNotNull($address = $this->page->find('css', '#tenant-approve-property-popup .addressDiv'));
        $this->assertEquals($start, $editStart->getValue(), 'Wrong edit start');
        $this->assertEquals($finish, $editFinish->getValue(), 'Wrong edit finish');
        $this->assertEquals(7677.00, $amount->getValue(), 'Wrong edit amount');
        $this->assertEquals('770 Broadway, Manhattan #2-e', $address->getHtml(), 'Wrong edit unit');

        if ($isIntegrated) {
            $this->assertNotNull($resident = $this->page->find('css', '#residentId'));
            $this->assertEquals('t12345', $resident->getValue(), 'Wrong edit resident id');
        }

        $this->page->pressButton('close');
        $this->assertNotNull($edit = $this->page->find('css', '.edit'));
        $edit->click();

        $this->session->wait($this->timeout, "$('#tenant-edit-property-popup').is(':visible')");
        $endAtRadio = $this->page->find('css', '#tenant-edit-property-popup .finishAtLabelM2M');
        $this->assertNotNull($endAtRadio);
        if ($isIntegrated) {
            $this->assertNotNull($resident = $this->page->find('css', '#resident-edit'));
            $this->session->evaluateScript(
                "$('#resident-edit').val(' ');"
            );
            $resident->setValue('t123457');
        }
        $endAtRadio->click();
        $this->page->pressButton('savechanges');
        $this->session->reload();
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($edit = $this->page->find('css', '.edit'));
        $edit->click();

        $this->session->wait($this->timeout, "$('#tenant-edit-property-popup').is(':visible')");
        $this->session->wait($this->timeout, "$('.loader').is(':visible') === false");
        // for find and check radio need show it (default "display:none")
        $this->session->evaluateScript('$(\'input[name="optionsFinishAtEdit"]\').show();');
        $checkedMonth2Month = $this->page->find('css', '#tenant-edit-property-popup .finishAtLabelM2M input');
        $this->assertNotNUll($checkedMonth2Month);
        $this->assertEquals('monthToMonth', $checkedMonth2Month->getValue());
        $this->assertEquals('true', $checkedMonth2Month->getAttribute('checked'));
        if ($isIntegrated) {
            $this->assertNotNull($resident = $this->page->find('css', '#resident-edit'));
            $this->assertEquals('t123457', $resident->getValue(), 'Wrong edit resident id');
        }
        $this->logout();

        $contracts = $em->getRepository('RjDataBundle:Contract')->findBy(
            array(
                'rent'      => 7677.00
            )
        );
        $this->assertCount(1, $contracts, 'Wrong count contract');

        /**
         * @var $contract Contract
         */
        $contract = reset($contracts);
        if ($isIntegrated) {
            $this->assertEquals(200.00, $contract->getIntegratedBalance(), 'Wrong balance');
            $this->assertEquals(0, $contract->getBalance(), 'Wrong balance');
        } else {
            $this->assertEquals(0, $contract->getBalance(), 'Wrong balance');
            $this->assertEquals(0, $contract->getIntegratedBalance(), 'Wrong balance');
        }
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
        $this->session->wait($this->timeout + 15000, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals(self::ALL, $allh2->getText(), 'Wrong count of tenants');
        $this->assertNotNull($approve = $this->page->find('css', '.approve'));
        $approve->click();
        $this->page->pressButton('edit.Info');
        $this->page->clickLink('remove.tenant');
        $this->page->pressButton('yes.remove.contract');
        $this->session->wait($this->timeout, "!$('#contract-remove-popup').is(':visible')");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals(self::ALL_MINUS_ONE, $allh2->getText(), 'Wrong count');
        $this->logout();
        //Check email notify tenant about removed contract by landlord
        $this->assertCount(1, $this->getEmails(), 'Wrong number of emails');
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
        $this->session->wait($this->timeout+2000, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals(self::ALL, $allh2->getText(), 'Wrong count of tenants');

        $this->assertNotNull($searchField = $this->page->find('css', '#searchPaymentsStatus_link'));
        $searchField->click();
        $this->assertNotNull($current = $this->page->find('css', '#searchPaymentsStatus_li_4'));
        $current->click();
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments-status'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (8)', $allh2->getText(), 'Wrong count of tenants');
        $this->assertNotNull($approve = $this->page->find('css', '.edit'));
        $approve->click();
        $this->page->clickLink('end.br.contract');
        $this->assertNotNull($outstandingBalance = $this->page->find('css', '.outstandingBalance'));
        $outstandingBalance->setValue(223.21);
        $this->page->pressButton('yes.end.contract');
        $this->session->wait($this->timeout, "!$('#tenant-end-contract').is(':visible')");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (7)', $allh2->getText(), 'Wrong count');
        $this->logout();
        //Check email notify tenant about removed contract by landlord
        $this->assertCount(1, $this->getEmails(), 'Wrong number of emails');

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
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals(self::ALL, $allh2->getText(), 'Wrong count');
        $this->assertNotNull($searchField = $this->page->find('css', '#searchPaymentsStatus_link'));
        $searchField->setValue('contract.status.pending');
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments-status'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (2)', $allh2->getText(), 'Wrong count');
        $this->logout();
    }

    /**
     * @test
     * @dataProvider providerEdit
     */
    public function addTenantNoneExist($isIntegrated)
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /**
         * @var $group Group
         */
        $group = $em->getRepository('DataBundle:Group')->findOneByName('Sea side Rent Group');
        $setting = $group->getGroupSettings();
        $setting->setIsIntegrated($isIntegrated);
        $em->persist($setting);
        $em->flush();
        $em->clear();
        $this->clearEmail();
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        // set group - "Sea side Rent Group" to be able to change isIntegrated setting
        $this->assertNotNull($select = $this->page->find('css', '.group-select>a'));
        $select->click();
        $this->assertNotNull($selectOption = $this->page->find('css', '#holding-group_li_1>span'));
        $selectOption->click();
        $this->session->wait(1000, "false"); // wait refresh page
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");

        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (4)', $allh2->getText(), 'Wrong count');
        $this->page->pressButton('add.tenant');
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype'));
        $this->page->pressButton('invite.tenant');
        $this->session->wait(1500, "false"); // wait refresh page
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(1, $errorList, 'Wrong number of errors');

        $formField = array(
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_first_name' => 'Alex',
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_last_name' => 'Sharamko',
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_phone' => '7858655392',
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_email' => 'test@email.ru',
            'rentjeeves_landlordbundle_invitetenantcontracttype_contract_rent' => '200',
            'rentjeeves_landlordbundle_invitetenantcontracttype_contract_finishAtType_1' => true,
            'rentjeeves_landlordbundle_invitetenantcontracttype_contract_dueDate' => 23,
        );

        if ($isIntegrated) {
            $formField += array('rentjeeves_landlordbundle_invitetenantcontracttype_resident_residentId' => 't0013534');
            $this->fillForm($form, $formField);

            $this->page->pressButton('invite.tenant');
            //Check created contract
            $this->session->wait(1000, "false");
            $this->assertNotNull($error = $this->page->find('css', '.attention-box>ul>li'));
            $this->assertEquals('add_or_edit_tenants.error.already_exist', $error->getHtml(), 'Wrong resident id');
            unset($formField['rentjeeves_landlordbundle_invitetenantcontracttype_resident_residentId']);
            $formField += array('rentjeeves_landlordbundle_invitetenantcontracttype_resident_residentId' => 'test1234');
        }

        $this->fillForm($form, $formField);

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
        $this->session->wait($this->timeout, "!$('#tenant-add-property-popup').is(':visible')");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (5)', $allh2->getText(), 'Wrong count');
        $this->assertNotNull($searchField = $this->page->find('css', '#searchPaymentsStatus_link'));
        $searchField->setValue('contract.status.invite');
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments-status'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (2)', $allh2->getText(), 'Wrong count');
        $this->logout();
        // end

        $this->setDefaultSession('goutte');
        $emails = $this->getEmails();
        $this->assertCount(1, $emails, 'Wrong number of emails');
        $email = $this->getEmailReader()->getEmail(array_pop($emails))->getMessage('text/html');
        $crawler = $this->getCrawlerObject($email->getBody());
        $url = $crawler->filter('#payRentLink')->getNode(0)->getAttribute('href');

        $this->session->visit($url);
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
        if ($isIntegrated) {
            $resident = $em->getRepository('RjDataBundle:ResidentMapping')->findOneBy(
                array(
                    'residentId' => 'test1234',
                )
            );
            $this->assertNotNull($resident, 'wrong number of contracts');
        }
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
        $this->assertNull($contract->getTransUnionStartAt());
        $this->assertFalse($contract->getReportToTransUnion());
        $this->logout();
        //Test identification
        $this->setDefaultSession('selenium2');
        $this->login('test@email.ru', 'pass');
        $this->assertNotNull(
            $close = $this->page->find('css', '.ui-dialog-titlebar-close')
        );
        $close->click();

        $this->page->clickLink('tabs.summary');
        $this->session->wait($this->timeout+5000, "typeof $ !== undefined");
        $this->assertNotNull(
            $form = $this->page->find('css', '#rentjeeves_checkoutbundle_userdetailstype')
        );
        $this->session->evaluateScript("$('#ssn_rentjeeves_checkoutbundle_userdetailstype_ssn_ssn1').val('666')");
        $this->session->evaluateScript("$('#ssn_rentjeeves_checkoutbundle_userdetailstype_ssn_ssn2').val('30')");
        $this->session->evaluateScript("$('#ssn_rentjeeves_checkoutbundle_userdetailstype_ssn_ssn3').val('9041')");


        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_userdetailstype_new_address_street'  => 'Street',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_city'    => 'City',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_area'    => 'CA',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_zip'     => '90210',
            )
        );
        $this->page->pressButton('pay_popup.step.next');
        $this->assertNotNull($form = $this->page->find('css', '#questions'));
        //Fill correct answer
        $this->fillForm(
            $form,
            array(
                'questions_OutWalletAnswer1_0' => true,
                'questions_OutWalletAnswer2_1' => true,
                'questions_OutWalletAnswer3_2' => true,
                'questions_OutWalletAnswer4_3' => true,
            )
        );
        $this->page->pressButton('pay_popup.step.3');
        $this->assertNotNull($loading = $this->page->find('css', '.loading'));
        $this->session->wait($this->timeout+5000, "window.location.pathname.match('\/summary') === null");
        $em->refresh($contract);
        $this->assertNotNull($contract->getTransUnionStartAt());
        $this->assertTrue($contract->getReportToTransUnion());
    }


    /**
     * @test
     * @depends addTenantNoneExist
     */
    public function tenantPay()
    {
        $this->setDefaultSession('selenium2');
        $this->login('test@email.ru', 'pass');
        $this->assertNotNull($payButton = $this->page->find('css', '.button-contract-pay'));
        $payButton->click();
        $this->assertNotNull($payPopup = $this->page->find('css', '#pay-popup'));
        $this->assertNotNull($payPopup = $payPopup->getParent());

        $this->assertNotNull(
            $propertyAddress = $this->page->find(
                'css',
                '#rentjeeves_checkoutbundle_paymenttype_property_address'
            )
        );

        $this->assertNotNull($closeButton = $payPopup->find('css', '.ui-dialog-titlebar-close'));
        $closeButton->click();

        $this->page->pressButton('contract-pay-1');

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length"
        );
        $paidFor = $this->page->findAll('css', '#rentjeeves_checkoutbundle_paymenttype_paidFor option');
        $paidFor = $paidFor[1]->getAttribute('value');
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymenttype_amount'      => '0',
                'rentjeeves_checkoutbundle_paymenttype_type'        => PaymentTypeEnum::RECURRING,
                'rentjeeves_checkoutbundle_paymenttype_dueDate'     => '28',
                'rentjeeves_checkoutbundle_paymenttype_startMonth'  => 2,
                'rentjeeves_checkoutbundle_paymenttype_startYear'   => date('Y')+1,
                'rentjeeves_checkoutbundle_paymenttype_amount'      => '1500',
                'rentjeeves_checkoutbundle_paymenttype_paidFor'     => $paidFor,
            )
        );


        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#id-source-step:visible').length"
        );


        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentaccounttype');

        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'Test payment',
                'rentjeeves_checkoutbundle_paymentaccounttype_PayorName' => 'Timothy APPLEGATE',
                'rentjeeves_checkoutbundle_paymentaccounttype_RoutingNumber' => '062202574',
                'rentjeeves_checkoutbundle_paymentaccounttype_AccountNumber_AccountNumber' => '123245678',
                'rentjeeves_checkoutbundle_paymentaccounttype_AccountNumber_AccountNumberAgain' => '123245678',
                'rentjeeves_checkoutbundle_paymentaccounttype_ACHDepositType_0' => true,
            )
        );


        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout+ 85000, // local need more time for passed test
            "!jQuery('#id-source-step').is(':visible')"
        );


        $this->session->wait(
            $this->timeout,
            "jQuery('button:contains(checkout.make_payment)').is(':visible')"
        );
        $payPopup->pressButton('checkout.make_payment');

        $this->session->wait(
            $this->timeout + 10000,
            "!jQuery('#pay-popup:visible').length"
        );

        $this->assertNotNull($pay = $this->page->find('css', '#pay-popup'));
        $this->assertFalse($pay->isVisible());
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
                'email' => 'test@email.ru',
            )
        );

        $contracts = $tenant->getContracts();
        $this->assertCount(1, $contracts, 'wrong number of contracts');

        /**
         * @var $contract Contract
         */
        $contract = $contracts->get(0);
        $this->assertNotNull($contract->getTransUnionStartAt());
        $this->assertTrue($contract->getReportToTransUnion());
    }

    /**
     * @test
     * @dataProvider providerEdit
     */
    public function addTenantExist($isIntegrated)
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->clearEmail();
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /**
         * @var $group Group
         */
        $group = $em->getRepository('DataBundle:Group')->findOneByName('Sea side Rent Group');
        $setting = $group->getGroupSettings();
        $setting->setIsIntegrated($isIntegrated);
        $em->persist($setting);
        $em->flush();
        $em->clear();
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        // set group - "Sea side Rent Group"
        $this->assertNotNull($select = $this->page->find('css', '.group-select>a'));
        $select->click();
        $this->assertNotNull($selectOption = $this->page->find('css', '#holding-group_li_1>span'));
        $selectOption->click();
        $this->session->wait(1000, "false"); // wait refresh page
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");

        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (4)', $allh2->getText(), 'Wrong count');
        $this->page->pressButton('add.tenant');
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype'));
        $this->page->pressButton('invite.tenant');
        $this->session->wait(1000, "false"); // wait refresh page
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(1, $errorList, 'Wrong number of errors');

        $formField = [
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_first_name' => 'Alex',
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_last_name' => 'Sharamko',
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_phone' => '7858655392',
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_email' => 'robyn@rentrack.com',
            'rentjeeves_landlordbundle_invitetenantcontracttype_contract_rent' => '200',
            'rentjeeves_landlordbundle_invitetenantcontracttype_contract_dueDate' => 13,
        ];
        // add resident id
        if ($isIntegrated) {
            $formField += ['rentjeeves_landlordbundle_invitetenantcontracttype_resident_residentId' => 'test12345'];
        }
        $this->fillForm($form, $formField);

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
        $this->session->wait($this->timeout, "!$('#tenant-add-property-popup').is(':visible')");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (5)', $allh2->getText(), 'Wrong count');
        $this->assertNotNull($searchField = $this->page->find('css', '#searchPaymentsStatus_link'));
        $searchField->setValue('contract.status.approved');
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments-status'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
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
        if ($isIntegrated) {
            $resident = $em->getRepository('RjDataBundle:ResidentMapping')->findOneBy(
                [
                    'residentId' => 'test12345',
                ]
            );
            $this->assertNotNull($resident, 'wrong number of contracts');
        }
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
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals(self::ALL, $allh2->getText(), 'Wrong count');
        $this->page->pressButton('add.tenant');
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype'));
        $this->page->pressButton('invite.tenant');
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(1, $errorList, 'Wrong number of errors');
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
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        //Check created contracts
        $this->assertNotNull($search = $this->page->find('css', '#searchPaymentsStatus_link'));
        $search->click();
        $this->assertNotNull($inviteStatus = $this->page->find('css', '#searchPaymentsStatus_li_2'));
        $inviteStatus->click();

        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments-status'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");

        $this->assertNotNull($all = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (1)', $all->getText(), 'Wrong count');

        $this->assertNotNull($review = $this->page->find('css', 'a.edit'));
        $review->click();
        $this->assertNotNull($sendReminder = $this->page->find('css', '.sendReminder'));
//        $this->session->wait($this->timeout, "$('.loader').is(':visible')");
        $this->session->wait($this->timeout, "!$('.loader').is(':visible')");
        $sendReminder->click();
//        $this->session->wait($this->timeout, "$('.overlay-trigger').is(':visible')");
        $this->session->wait($this->timeout, "!$('.overlay-trigger').is(':visible')");
        $sendReminder->click();

        $this->session->wait($this->timeout, "$('.default>li').text() == 'contract.reminder.error.already.send'");

        $this->assertNotNull($error = $this->page->find('css', '.default>li'));
        $this->assertEquals('contract.reminder.error.already.send', $error->getText(), 'Wrong text error');
        $this->logout();
        // check email
        $this->assertCount($nCountEmails, $this->getEmails(), 'Wrong number of emails');
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
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals(self::ALL, $allh2->getText(), 'Wrong count');
        $this->page->pressButton('add.tenant');
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype'));
        $this->page->pressButton('invite.tenant');
        $this->session->wait(1000, "false"); // wait refresh page
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(1, $errorList, 'Wrong number of errors');

        $formField = array(
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_first_name' => 'Alex',
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_last_name' => 'Sharamko',
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_phone' => '7858655392',
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_email' => 'test123@email.ru',
            'rentjeeves_landlordbundle_invitetenantcontracttype_contract_rent' => '200',
            'rentjeeves_landlordbundle_invitetenantcontracttype_resident_residentId' => 'test12345',
        );

        $this->fillForm($form, $formField);

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
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        //Check created contracts
        $this->assertNotNull($search = $this->page->find('css', '#searchPaymentsStatus_link'));
        $search->click();
        $this->assertNotNull($inviteStatus = $this->page->find('css', '#searchPaymentsStatus_li_2'));
        $inviteStatus->click();
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments-status'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");

        $this->assertNotNull($all = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (1)', $all->getText(), 'Wrong count');

        $this->assertNotNull($review = $this->page->find('css', 'a.edit'));
        $review->click();
        
        $this->page->clickLink('revoke.inv');
        $this->page->pressButton('yes.revoke.inv');

        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        //Check created contracts
        $this->assertNotNull($search = $this->page->find('css', '#searchPaymentsStatus_link'));
        $search->click();
        $this->assertNotNull($inviteStatus = $this->page->find('css', '#searchPaymentsStatus_li_2'));
        $inviteStatus->click();
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments-status'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#contracts-block .notHaveData').length > 0");

        $this->assertNotNull($all = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (0)', $all->getText(), 'Wrong count');
        $this->logout();
        //Check email notify tenant about removed contract by landlord
        $this->assertCount(1, $this->getEmails(), 'Wrong number of emails');
    }
}
