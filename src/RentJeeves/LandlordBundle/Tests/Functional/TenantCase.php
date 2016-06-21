<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use CreditJeeves\DataBundle\Model\User;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class TenantCase extends BaseTestCase
{
    protected $timeout = 35000;

    const ALL = 'All (18)';
    const ALL_PLUS_ONE = 'All (19)';
    const ALL_MINUS_ONE = 'All (17)';

    protected function loadTenantTab()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->session->getDriver()->resizeWindow(1600, 1200);

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');

        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
    }

    /**
     * Approve contract for tenant
     * @test
     */
    public function approve()
    {
        $this->loadTenantTab();
        $this->assertNotEmpty(
            $contractPendings = $this->page->findAll('css', '.contract-pending>div'),
            'Check fixtures, landlord1@example.com should have pending contracts on first page of tenant tab.'
        );
        $this->assertCount(
            3,
            $contractPendings,
            'Check fixtures, landlord1@example.com should have 3 pending contracts on first page of tenant tab.'
        );
        $this->assertEquals(
            'contract.statuses.pending',
            $contractPendings[0]->getHtml(),
            'Check fixtures, first pending contract should have status "contract.statuses.pending"'
        );
        $this->assertEquals(
            'contract.statuses.pending',
            $contractPendings[1]->getHtml(),
            'Check fixtures, second pending contract should have status "contract.statuses.pending"'
        );
        $this->assertEquals(
            'contract.statuses.contract_ended',
            $contractPendings[2]->getHtml(),
            'Check fixtures, third pending contract should have status "contract.statuses.contract_ended"'
        );
        $this->assertNotEmpty(
            $approves = $this->page->findAll('css', '.approve'),
            'Button "Approve" for contract doesn\'t found'
        );
        $this->assertCount(3, $approves);
        $approves[0]->click(); // open dialog
        $this->page->pressButton('approve.tenant');
        $this->session->wait($this->timeout, "$('.overlay').is(':visible')");
        $this->session->wait($this->timeout, "!$('.overlay').is(':visible')");
        $this->assertNotEmpty(
            $errors = $this->page->findAll('css', 'div.attention-box ul.default li'),
            'Should show errors'
        );
        $this->assertCount(1, $errors, 'Wrong number of errors');
        $this->assertNotNull(
            $start = $this->page->find('css', '#contractApproveStart'),
            'Start on date field not found'
        );
        $start->click();
        $this->session->wait($this->timeout, "$('#ui-datepicker-div .ui-datepicker-today').is(':visible')");
        $this->assertNotNull($today = $this->page->find('css', '#ui-datepicker-div .ui-datepicker-today'));
        $today->click();
        $this->assertNotNull(
            $amount = $this->page->find('css', '#amount-approve'),
            'Amount field not found'
        );
        $amount->setValue('200');
        $this->page->pressButton('approve.tenant');
        $this->session->wait($this->timeout, "!$('#tenant-approve-property-popup').is(':visible')");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull(
            $propertiesTable = $this->page->find('css', '.properties-table'),
            'Contract table can not load'
        );
        $this->assertNotEmpty(
            $contractPendings = $this->page->findAll('css', '.contract-pending>div'),
            'Landlord should have pending contracts on first page'
        );
        $this->assertCount(
            2,
            $contractPendings,
            'Wrong number of pending contract, after approve should have just 2 pending contracts on first page'
        );

        $this->logout();
    }

    /**
     * Check sorting for contract list on tenant tab
     * @test
     */
    public function sort()
    {
        $this->loadTenantTab();
        $this->assertNotEmpty(
            $td = $this->page->findAll('css', 'td'),
            'Check fixtures, landlord1@example.com should have contracts.'
        );
        $this->assertEquals(
            'Timothy Applegate',
            $td[1]->getText(),
            'Check fixtures, first contract should have tenant name "Timothy Applegate"'
        );

        $this->assertNotNull(
            $tenant = $this->page->find('css', '#first_name'),
            'Column header with first name sort clickable btn not found'
        );
        $tenant->click();
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull(
            $this->page->find('css', '#contracts-block .properties-table'),
            'Contract list table should be visible'
        );
        $this->assertNotEmpty(
            $td = $this->page->findAll('css', 'td'),
            'Contract list should not be empty'
        );
        $this->assertEquals(
            'William Johnson',
            $td[1]->getText(),
            'After first sorting first contract should have tenant name "William Johnson"(sort desc)'
        );
        $tenant->click(); // click again and sort asc
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull(
            $this->page->find('css', '#contracts-block .properties-table'),
            'Contract list table should be visible'
        );
        $this->assertNotEmpty(
            $td = $this->page->findAll('css', 'td'),
            'Contract list should not be empty'
        );
        $this->assertEquals(
            'Alex Jordan',
            $td[1]->getText(),
            'After second sorting first contract should have tenant name "Alex Jordan"(sort asc)'
        );

        $this->logout();
    }

    /**
     * @return array
     */
    public function providerEdit()
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @test
     * @dataProvider providerEdit
     *
     * @param $isIntegrated
     */
    public function edit($isIntegrated)
    {
        $this->loadTenantTab();
        // Prepare Group for test
        $em = $this->getEntityManager();
        /** @var $group Group */
        $group = $em->getRepository('DataBundle:Group')->findOneByName('Sea side Rent Group');
        $setting = $group->getGroupSettings();
        $setting->setIsIntegrated($isIntegrated);
        $em->persist($setting);
        $em->flush();

        $this->assertNotNull($select = $this->page->find('css', '.group-select>a'), 'Can not find group selector');
        $select->click();

        $this->assertNotNull(
            $selectOption = $this->page->find('css', '#holding-group_li_1>span'),
            'Can not find group option in group selector'
        );
        $selectOption->click();
        $this->session->wait($this->timeout - 25000, "false"); // wait refresh page, try set less time

        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($approve = $this->page->find('css', '.approve'), 'Can not find Approve button');
        $approve->click();

        $this->session->wait($this->timeout, "$('#tenant-approve-property-popup .footer-button-box').is(':visible')");

        // Check that amount refresh from server each time after close and open dialog
        $this->assertNotNull($amountInput = $this->page->find('css', '#amount-approve'), 'Can not find amount input');
        $amount = $amountInput->getValue();
        $amountInput->setValue('999999999TEST');
        $this->assertNotNull(
            $closeBtn = $this->page->find('css', '.ui-dialog-titlebar-close'),
            'Can not find close button'
        );
        $closeBtn->click();
        $approve->click();
        $this->session->wait($this->timeout, "$('#tenant-approve-property-popup .footer-button-box').is(':visible')");
        $this->session->wait($this->timeout, "$('.overlay').is(':visible')");
        $this->session->wait($this->timeout, "!$('.overlay').is(':visible')");
        $this->assertEquals($amount, $amountInput->getValue(), 'Amount should load from server and set again');

        $this->page->pressButton('edit.Info');
        $this->session->wait(
            $this->timeout + 3000,
            "$('#unit-edit').is(':visible') && $('#amount-edit').is(':visible')"
        );
        if ($isIntegrated) {
            $this->assertNotNull(
                $resident = $this->page->find('css', '#resident-edit'),
                'Can not find resident field on the page'
            );
            $resident->setValue('t12345');
        }

        // click next_payment_date and select today
        $start = $this->page->find('css', '#contractEditStart');
        $this->assertNotNull($start, 'Can not find #contractEditStart on the page');
        $start->click();

        $this->session->wait($this->timeout, "$('#ui-datepicker-div').is(':visible')");

        $today = $this->page->find('css', '#ui-datepicker-div .ui-datepicker-today');
        $this->assertNotNull($today, 'Can not find datepicker value for today');
        $today->click();
        $this->session->wait($this->timeout, "!$('#ui-datepicker-div').is(':visible')");
        // end selected next_payment_date

        $this->assertNotNull($amount = $this->page->find('css', '#amount-edit'), 'Can not find amount input');
        $amount->setValue(7677.00);
        // choose input radio ON, and select date finish

        $endAtRadio = $this->page->find('css', '#tenant-edit-property-popup .finishAtLabel');
        $this->assertNotNull($endAtRadio, 'Can not find finishAt radio button');
        $endAtRadio->click();

        $finish = $this->page->find('css', '#contractEditFinish');
        $this->assertNotNull($finish, 'Can not find finish contract popup');
        $finish->click();
        $this->session->wait($this->timeout, "$('#ui-datepicker-div').is(':visible')");

        $next = $this->page->find('css', '#ui-datepicker-div .ui-datepicker-next');
        $this->assertNotNull($next, 'Can not find NEXT on the datepicker');
        $next->click();

        $future = $this->page->findAll('css', '#ui-datepicker-div .ui-state-default');
        $this->assertNotNull($future, 'Can not find FUTURE on the datepicker');
        $future[count($future) - 1]->click();
        // end select finish date

        $this->assertNotNull(
            $contractEditStart = $this->page->find('css', '#contractEditStart'),
            'Can not find #contractEditStart on the page'
        );
        $start = $contractEditStart->getValue();

        $this->assertNotNull($contractEditStart = $this->page->find('css', '#contractEditFinish'));
        $finish = $contractEditStart->getValue();

        $this->assertNotNull(
            $unitEdit = $this->page->find('css', '#unit-edit'),
            'Can not find UNIT select box'
        );
        $unitEdit->selectOption('2-e');

        $this->assertNotNull(
            $dueDateEdit = $this->page->find('css', '.dueDateEdit'),
            'Can not find dueDate select box'
        );
        $dueDateEdit->selectOption('14');

        if ($isIntegrated) {
            $this->assertNotNull(
                $balanceField = $this->page->find('css', '.balance-field'),
                'Can not find balance field'
            );
            $balanceField->setValue('200.00');
        }

        $this->page->pressButton('savechanges');
        $this->session->wait($this->timeout, "!$('#tenant-edit-property-popup').is(':visible')");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($approve = $this->page->find('css', '.approve'), 'Can not find APPROVE button');
        $approve->click();

        $this->assertNotNull(
            $editStart = $this->page->find('css', '#contractApproveStart'),
            'Can not find contract start'
        );
        $this->assertNotNull(
            $editFinish = $this->page->find('css', '#contractApproveFinish'),
            'Can not find contract finish'
        );
        $this->assertNotNull($amount = $this->page->find('css', '#amount-approve'), 'Can not find contract amount');
        $this->assertNotNull(
            $address = $this->page->find('css', '#tenant-approve-property-popup .addressDiv'),
            'Can not find contract address'
        );
        $this->assertEquals($start, $editStart->getValue(), 'Wrong start date after edit');
        $this->assertEquals($finish, $editFinish->getValue(), 'Wrong finish date after edit');
        $this->assertEquals(7677.00, $amount->getValue(), 'Wrong amount after edit');
        $this->assertEquals('770 Broadway #2-e', $address->getHtml(), 'Wrong unit after edit');

        if ($isIntegrated) {
            $this->assertNotNull($resident = $this->page->find('css', '#residentId'), 'Can not find resident');
            $this->assertEquals('t12345', $resident->getValue(), 'Wrong resident id after edit');
        }

        $this->page->pressButton('close');
        $this->assertNotNull($edit = $this->page->find('css', '.edit'), 'Can not find contract edit button');
        $edit->click();

        $this->session->wait($this->timeout, "$('#tenant-edit-property-popup').is(':visible')");
        $endAtRadio = $this->page->find('css', '#tenant-edit-property-popup .finishAtLabelM2M');
        $this->assertNotNull($endAtRadio, 'Can not find contract endAt radio button');
        if ($isIntegrated) {
            $this->assertNotNull($resident = $this->page->find('css', '#resident-edit'), 'Can not find resident field');
            $resident->setValue('t123457');
        }
        $endAtRadio->click(); // set End of contract to 'When Cancelled'

        $this->getDomElement('#payment_allowed_toggle .toggle-on.active', 'Payments should be allowed');
        $paymentAllowedToggle = $this->getDomElement('#payment_allowed_toggle .toggle-slide');
        $paymentAllowedToggle->click();

        $this->page->pressButton('savechanges');
        $this->session->reload();
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($edit = $this->page->find('css', '.edit'), 'Can not find contract edit button');
        $edit->click();

        $this->session->wait($this->timeout, "$('#tenant-edit-property-popup').is(':visible')");
        $this->session->wait($this->timeout, "!$('.loader').is(':visible')");
        // for find and check radio need show it (default "display:none")
        $this->session->evaluateScript('$(\'#tenant-edit-property-popup label checkbox input[type="radio"]\').show();');
        $checkedMonth2Month = $this->page->find(
            'css',
            '#tenant-edit-property-popup input.monthToMonth'
        );
        $this->assertNotNull($checkedMonth2Month, 'MonthToMonth should not be null');
        $this->assertNotNull($checkedMonth2Month->getAttribute('checked'), 'MonthToMonth should have attr CHECKED');
        $this->assertEquals('monthToMonth', $checkedMonth2Month->getValue(), 'MonthToMonth value is wrong');
        $this->assertEquals('true', $checkedMonth2Month->getAttribute('checked'), 'MonthToMonth should be checked');
        $this->getDomElement('#payment_allowed_toggle .toggle-off.active', 'Payments should be disallowed');
        if ($isIntegrated) {
            $this->assertNotNull($resident = $this->page->find('css', '#resident-edit'), 'Can not find resident field');
            $this->assertEquals('t123457', $resident->getValue(), 'Wrong edit resident id');
        }
        $this->logout();

        $contracts = $em->getRepository('RjDataBundle:Contract')->findBy(['rent' => 7677.00]);
        $this->assertCount(1, $contracts, 'Wrong count contract. Should be 1.');

        /**
         * @var Contract $contract
         */
        $contract = reset($contracts);
        if ($isIntegrated) {
            $this->assertEquals(200.00, $contract->getIntegratedBalance(), 'Wrong integrated balance');
        } else {
            $this->assertEquals(0, $contract->getIntegratedBalance(), 'Wrong integrated balance');
        }
    }

    /**
     * @test
     */
    public function remove()
    {
        $this->load(true);
        $this->clearEmail();
        $this->setDefaultSession('selenium2');
        $this->loginByAccessToken('landlord1@example.com', $this->getUrl() . 'landlord/tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout + 15000, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals(self::ALL, $allh2->getText(), 'Wrong count of tenants');
        $this->assertNotNull($approve = $this->page->find('css', '.approve'));
        $approve->click();
        $this->page->pressButton('edit.Info');
        $this->page->clickLink('remove.lease');
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
        $this->loginByAccessToken('landlord1@example.com', $this->getUrl() . 'landlord/tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout + 2000, "$('#contracts-block .properties-table').length > 0");
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
                'status' => 'finished',
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
        $this->loginByAccessToken('landlord1@example.com', $this->getUrl() . 'landlord/tenants');
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
        $this->session->wait(5000, "false"); // wait refresh page
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");

        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (4)', $allh2->getText(), 'Wrong count');
        $this->page->pressButton('add.tenant');
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype'));
        $this->page->pressButton('invite.tenant');
        $this->session->wait(3500, "false"); // wait refresh page

        $this->chooseLinkSelect('rentjeeves_landlordbundle_invitetenantcontracttype_contract_property', '1');

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
            $this->session->wait(12000, "$('.attention-box>ul>li').length === 1");
            $this->assertNotNull($error = $this->page->find('css', '.attention-box>ul>li'));
            $this->assertEquals('error.residentId.already_use', $error->getHtml(), 'Wrong resident id');
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
        $future[count($future) - 1]->click();

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
                'rentjeeves_publicbundle_tenanttype_password_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_tos' => true,
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

        $this->setDefaultSession('selenium2');
        $this->login('test@email.ru', 'pass');
        $this->assertNotNull(
            $this->page->find('css', '#pay-popup'),
            'Should be displayed payment wizard dialog after login'
        );
    }

    /**
     * @test
     * @depends addTenantNoneExist
     */
    public function checkDuplicateContract()
    {
        # use already created tenant on depends test
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');

        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        // set group - "Sea side Rent Group" to be able to change isIntegrated setting
        $this->assertNotNull($select = $this->page->find('css', '.group-select>a'));
        $select->click();
        $this->assertNotNull($selectOption = $this->page->find('css', '#holding-group_li_1>span'));
        $selectOption->click();
        $this->session->wait(5000, "false"); // wait refresh page
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");

        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (5)', $allh2->getText(), 'Wrong count');

        $this->page->pressButton('add.tenant');
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype'));

        $formFields = [
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_first_name' => 'Alex',
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_last_name' => 'Sharamko',
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_email' => 'test@email.ru',
            'rentjeeves_landlordbundle_invitetenantcontracttype_contract_rent' => '200',
            'rentjeeves_landlordbundle_invitetenantcontracttype_contract_finishAtType_1' => true,
            'rentjeeves_landlordbundle_invitetenantcontracttype_contract_dueDate' => 23,
        ];

        $this->fillForm($form, $formFields);

        $this->chooseLinkSelect('rentjeeves_landlordbundle_invitetenantcontracttype_contract_property', '1');

        $this->session->wait(1000, "false"); // wait filling form from DB

        $this->page->pressButton('invite.tenant');
        $this->session->wait(1000, "false"); // wait refresh page
        $this->assertNotNull(
            $errorList = $this->page->findAll('css', '#tenant-add-property-popup .attention-box.pie-el ul.default>li')
        );
        $this->assertCount(1, $errorList, 'Wrong number of errors');
        $this->assertEquals('error.contract.duplicate', $errorList[0]->getHtml());
        $this->logout();
    }

    /**
     * @test
     * @depends addTenantNoneExist
     */
    public function tenantPay()
    {
        /** @var Tenant $tenant */
        $tenant = $this->getEntityManager()->getRepository('RjDataBundle:Tenant')->findOneByEmail('test@email.ru');
        $tenant->setIsVerified(UserIsVerified::PASSED);
        $this->getEntityManager()->flush($tenant);
        $this->setDefaultSession('selenium2');
        $this->login('test@email.ru', 'pass');

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
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::RECURRING,
                'rentjeeves_checkoutbundle_paymenttype_dueDate' => '28',
                'rentjeeves_checkoutbundle_paymenttype_startMonth' => 2,
                'rentjeeves_checkoutbundle_paymenttype_startYear' => date('Y') + 1,
                'rentjeeves_checkoutbundle_paymenttype_amount' => '1500',
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $paidFor,
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
                'rentjeeves_checkoutbundle_paymentaccounttype_type_1' => true,
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
            $this->timeout + 85000, // local need more time for passed test
            "!jQuery('#id-source-step').is(':visible')"
        );

        $this->session->wait(
            $this->timeout,
            "jQuery('button:contains(checkout.make_payment)').is(':visible')"
        );
        $payPopup->pressButton('checkout.make_payment');

        $this->session->wait(
            $this->timeout,
            "jQuery('button:contains(pay_popup.close)').is(':visible')"
        );
        $payPopup->pressButton('pay_popup.close');

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
        $this->assertNotNull(
            $errorList = $this->page->findAll('css', '#tenant-add-property-popup .attention-box.pie-el ul.default>li')
        );
        $errorCount = $isIntegrated ? 5 : 4;
        $this->assertCount($errorCount, $errorList, 'Wrong number of errors');

        $this->chooseLinkSelect('rentjeeves_landlordbundle_invitetenantcontracttype_contract_property', '1');

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
        $future[count($future) - 1]->click();

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
        $this->loginByAccessToken('landlord1@example.com', $this->getUrl() . 'landlord/tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, '$("img.processLoading").length <= 0');
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals(self::ALL, $allh2->getText(), 'Wrong count');
        $this->page->pressButton('add.tenant');
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype'));
        $this->page->pressButton('invite.tenant');
        $this->session->wait($this->timeout, '!$(".overlay").is(":visible")');
        $errors = $this->getDomElements('.attention-box.pie-el li', 'List of error should be shown');
        $this->assertCount(5, $errors);
        $this->fillForm(
            $form,
            [
                'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_email' => 'landlord1@example.com'
            ]
        );
        $this->page->pressButton('invite.tenant');
        $this->session->wait($this->timeout, '!$(".overlay").is(":visible")');
        $userExistMsgBox = $this->getDomElement('#userExistMessageLanlord');
        $this->assertTrue($userExistMsgBox->isVisible(), 'User exist message box should be visible');
        $this->assertEquals('user.itslandlord', $userExistMsgBox->getText());
        $this->logout();
    }

    private function sendReminder($nCountEmails = 1)
    {
        $this->setDefaultSession('selenium2');
        $this->loginByAccessToken('landlord1@example.com', $this->getUrl() . 'landlord/tenants');
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
        $this->assertEquals(
            'contract.reminder.error.already.send',
            $error->getText(),
            'Wrong text error : ' . $error->getText()
        );
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
        $this->loginByAccessToken('landlord1@example.com', $this->getUrl() . 'landlord/tenants');
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

        $this->chooseLinkSelect('rentjeeves_landlordbundle_invitetenantcontracttype_contract_property', '1');

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
        $future[count($future) - 1]->click();

        $this->page->pressButton('invite.tenant');
        $this->logout();

        $this->clearEmail();
        $this->sendReminder();
        $this->clearEmail();

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        /** @var $user User */
        $user = $em->getRepository('DataBundle:User')->findOneBy(array('email' => 'test123@email.ru'));
        $this->assertNotNull($user, 'User does not exist');
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
        $this->session->wait($this->timeout, '$("img.processLoading").length == 0');
        //Check created contracts
        $this->chooseLinkSelect('searchPaymentsStatus', 'invite');
        $searchSubmit = $this->getDomElement('#search-submit-payments-status');
        $searchSubmit->click();

        $this->session->wait($this->timeout, '$("img.processLoading").length == 0');

        $h2 = $this->getDomElement('.title-box>h2');
        $this->assertEquals('All (1)', $h2->getText(), 'Wrong count. Count should be 1, not ' . $h2->getText());

        $editLink = $this->getDomElement('a.edit');
        $editLink->click();

        $this->page->clickLink('revoke.inv');
        $this->page->pressButton('yes.revoke.inv');

        $this->session->wait($this->timeout, '$("img.processLoading").length == 0');
        //Check created contracts
        $searchSubmit = $this->getDomElement('#search-submit-payments-status');
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#contracts-block .notHaveData').length > 0");

        $h2 = $this->getDomElement('.title-box>h2');
        $this->assertEquals('All (0)', $h2->getText(), 'Wrong count. Count should be 0, not ' . $h2->getText());
        //Check email notify tenant about removed contract by landlord
        $this->assertCount(1, $this->getEmails(), 'Wrong number of emails');
    }

    /**
     * @test
     */
    public function searchTenantsInOtherGroups()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->loginByAccessToken('landlord1@example.com', $this->getUrl() . 'landlord/tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull(
            $allContractsCount = $this->page->find('css', '.title-box>h2'),
            'Empty box with numbers of contracts'
        );
        $this->assertEquals(self::ALL, $allContractsCount->getText());
        $this->assertNotNull(
            $searchColumn = $this->page->find('css', '#searchFilter_link'),
            'Empty filter selectbox'
        );
        $searchColumn->click();
        $this->assertNotNull(
            $optionEmail = $this->page->find('css', '#searchFilter_li_1'),
            'Empty options which should be email'
        );
        $optionEmail->click();
        $this->assertNotNull(
            $searchText = $this->page->find('css', '#searsh-field'),
            'Empty search input'
        );
        $searchText->setValue('rent');
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit'), 'Empty search button');
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull($allContractsCount = $this->page->find('css', '.title-box>h2'), 'Empty all contracts box');
        $this->assertEquals('All (14)', $allContractsCount->getText());
        $this->assertNotNull(
            $linkToTenantInDifferentGroups = $this->page->findAll('css', '#foundMoreContainer>a'),
            'We don\'t have links for groups which have result'
        );
        $this->assertCount(1, $linkToTenantInDifferentGroups, 'We didn\'t get correct number of links');
        $this->assertEquals('Sea side Rent Group', $linkToTenantInDifferentGroups[0]->getText());
        $linkToTenantInDifferentGroups[0]->click();
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotNull(
            $linkToTenantInDifferentGroups = $this->page->findAll('css', '#foundMoreContainer>a'),
            'We don\'t have links for groups which have result'
        );
        $this->assertCount(1, $linkToTenantInDifferentGroups, 'We didn\'t get correct number of links');
        $this->assertEquals('Test Rent Group', $linkToTenantInDifferentGroups[0]->getText());
        $this->logout();
    }

    /**
     * @return array
     */
    public function providerForEditExternalLeaseId()
    {
        return [
            [AccountingSystem::MRI_BOSTONPOST],
            [AccountingSystem::AMSI],
            [AccountingSystem::PROMAS]
        ];
    }

    /**
     * @test
     * @dataProvider providerForEditExternalLeaseId
     */
    public function editContractAndAddLeaseId($accountingSystem)
    {
        $this->loadTenantTab();
        // Prepare Group for test
        $em = $this->getEntityManager();
        /** @var $group Group */
        $group = $em->getRepository('DataBundle:Group')->findOneByName('Test Rent Group');
        $this->assertNotEmpty($group, 'Group should exist in fixtures');
        $setting = $group->getGroupSettings();
        $setting->setIsIntegrated(true);
        $holding = $group->getHolding();
        $holding->setAccountingSystem($accountingSystem);
        $em->flush();

        $this->session->reload();
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        $this->assertNotEmpty($edits = $this->page->findAll('css', '.edit'), 'Can not find contract edit button');
        $this->assertArrayHasKey(0, $edits, 'Should have one element');
        $edits[0]->click();

        $this->session->wait($this->timeout, "$('#tenant-edit-property-popup').is(':visible')");
        $this->assertNotNull($lease = $this->page->find('css', '#leaseId-edit'), 'Can not find lease field');
        $externalLeaseId = 't1234572222';
        $lease->setValue($externalLeaseId);
        $this->page->pressButton('savechanges');
        $this->session->wait($this->timeout, "!$('#tenant-edit-property-popup').is(':visible')");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");
        /** @var Contract $contract */
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            ['externalLeaseId' => $externalLeaseId]
        );
        $this->assertNotEmpty($contract, 'Should be contract with such lease id');
    }

    /**
     * @test
     * @dataProvider providerForEditExternalLeaseId
     */
    public function inviteNewTenantWithExternalLeaseId($accountingSystem)
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $em = $this->getEntityManager();
        /** @var $group Group */
        $group = $em->getRepository('DataBundle:Group')->findOneByName('Sea side Rent Group');
        $setting = $group->getGroupSettings();
        $setting->setIsIntegrated(true);
        $group->getHolding()->setAccountingSystem($accountingSystem);
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
        $this->session->wait(5000, "false"); // wait refresh page
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table').length > 0");

        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (4)', $allh2->getText(), 'Wrong count');
        $this->page->pressButton('add.tenant');
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_landlordbundle_invitetenantcontracttype'));
        $this->page->pressButton('invite.tenant');
        $this->session->wait(3500, "false"); // wait refresh page

        $this->chooseLinkSelect('rentjeeves_landlordbundle_invitetenantcontracttype_contract_property', '1');

        $formField = [
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_first_name' => 'Alex',
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_last_name' => 'Sharamko',
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_phone' => '7858655392',
            'rentjeeves_landlordbundle_invitetenantcontracttype_tenant_email' => 'test@email.ru',
            'rentjeeves_landlordbundle_invitetenantcontracttype_contract_rent' => '200',
            'rentjeeves_landlordbundle_invitetenantcontracttype_contract_finishAtType_1' => true,
            'rentjeeves_landlordbundle_invitetenantcontracttype_contract_dueDate' => 23,
            'rentjeeves_landlordbundle_invitetenantcontracttype_contract_externalLeaseId' => '322323',
        ];

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
        $future[count($future) - 1]->click();

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
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(['externalLeaseId' => '322323']);
        $this->assertNotEmpty($contract, 'Contract not created');
    }

    /**
     * @test
     */
    public function shouldMoveContractOutOfWaitingAfterAddedEmail()
    {
        $this->loadTenantTab();

        $this
            ->getDomElement('.group-select>a', 'Can not find group selector')
            ->click();

        $this
            ->getDomElement(
                '#holding-group_list span:contains("First DTR Group")',
                'Can not find group option in group selector'
            )
            ->click();
        $this->session->wait($this->timeout - 32000, "false"); // wait refresh page, try set less time

        $this->session->wait($this->timeout, 'typeof jQuery != "undefined"');
        $this->session->wait($this->timeout, '$("#contracts-block .properties-table").length > 0');

        $editBtn = $this
            ->getDomElement('#contracts-block tr>td.actions-status>div:contains("contract.statuses.waiting")')
            ->getParent()
            ->getParent()
            ->find('css', '.edit');

        $this->assertNotNull($editBtn, 'Can not find edit button for waiting contract');

        $editBtn->click();
        $contractId = str_replace('edit-', '', $editBtn->getAttribute('id'));
        $this->assertNotEmpty($contractId, 'Contract id is empty');
        $em = $this->getEntityManager();
        $contract = $em->getRepository('RjDataBundle:Contract')->find($contractId);
        $this->assertNotNull($contract, 'Should be found contract by id #' . $contractId);
        $this->assertEquals(ContractStatus::WAITING, $contract->getStatus(), 'Contract should be waiting');
        $this->assertEmpty($contract->getTenant()->getEmail(), 'Tenant should not have email');

        $this->session->wait($this->timeout, '$("#tenant-edit-property-popup .footer-button-box").is(":visible")');

        $residentField = $this->getDomElement('#resident-edit', 'Resident id field not found');
        $residentField->setValue('test_dtr_resident_1');

        $startDate = $this->getDomElement('#contractEditStart', 'Can not find #contractEditStart on the page');
        $startDate->setValue((new \DateTime())->format('m/d/Y'));

        $emailField = $this->getDomElement('#email-edit', 'Email field not found');
        $emailView = $this->getDomElement('#email-view', 'Email span not found');
        $this->assertTrue($emailField->isVisible(), 'Email input should be visible');
        $this->assertFalse($emailView->isVisible(), 'Email input should be hidden');
        $emailField->setValue('testdtr@example.com');

        $this->page->pressButton('savechanges');
        $this->session->wait($this->timeout, '!$("#tenant-edit-property-popup").is(":visible")');
        $this->session->wait($this->timeout, '$("#contracts-block .properties-table").length > 0');

        $editBtn = $this->getDomElement('#edit-' . $contractId);
        $editBtn->click();
        $this->session->wait($this->timeout, '$("#tenant-edit-property-popup .footer-button-box").is(":visible")');

        $this->assertFalse($emailField->isVisible(), 'Email input should be hidden');
        $this->assertTrue($emailView->isVisible(), 'Email span should be visible');

        $this->assertEquals('testdtr@example.com', $emailView->getText(), 'Email should be saved');

        $em->clear();
        $contract = $em->getRepository('RjDataBundle:Contract')->find($contractId);
        $this->assertNotNull($contract, 'Contract should not be removed');
        $this->assertEquals(
            ContractStatus::APPROVED,
            $contract->getStatus(),
            'Contract should be moved from waiting to approved'
        );
        $this->assertEquals(
            'testdtr@example.com',
            $contract->getTenant()->getEmail(),
            'Should be updated email for tenant'
        );
    }
}
