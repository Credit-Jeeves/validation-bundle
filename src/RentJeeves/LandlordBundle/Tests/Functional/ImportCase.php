<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Operation;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\YardiPaymentAccepted;
use RentJeeves\DataBundle\Model\Unit;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as ImportMapping;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\CoreBundle\DateTime;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class ImportCase extends BaseTestCase
{
    protected $mapFile = array(
        '1' => ImportMapping::KEY_UNIT,
        '4' => ImportMapping::KEY_RESIDENT_ID,
        '5' => ImportMapping::KEY_TENANT_NAME,
        '7' => ImportMapping::KEY_RENT,
        '10'=> ImportMapping::KEY_MOVE_IN,
        '11'=> ImportMapping::KEY_LEASE_END,
        '12'=> ImportMapping::KEY_MOVE_OUT,
        '13'=> ImportMapping::KEY_BALANCE,
        '14'=> ImportMapping::KEY_EMAIL,
    );

    protected $mapMultiplePropertyFile = array(
        '1' => ImportMapping::KEY_RESIDENT_ID,
        '2' => ImportMapping::KEY_TENANT_NAME,
        '3' => ImportMapping::KEY_RENT,
        '4' => ImportMapping::KEY_BALANCE,
        '5' => ImportMapping::KEY_UNIT_ID,
        '6' => ImportMapping::KEY_STREET,
        '8' => ImportMapping::KEY_UNIT,
        '9' => ImportMapping::KEY_CITY,
        '10'=> ImportMapping::KEY_STATE,
        '11'=> ImportMapping::KEY_ZIP,
        '13'=> ImportMapping::KEY_MOVE_IN,
        '14'=> ImportMapping::KEY_LEASE_END,
        '15'=> ImportMapping::KEY_MOVE_OUT,
        '16'=> ImportMapping::KEY_MONTH_TO_MONTH,
        '17'=> ImportMapping::KEY_EMAIL,
    );

    protected function getFilePathByName($fileName)
    {
        $sep = DIRECTORY_SEPARATOR;
        $filePath = getcwd();
        $filePath .= $sep.'data'.$sep.'fixtures'.$sep.$fileName;
        return $filePath;
    }

    protected function waitReviewAndPost()
    {
        $this->session->wait(
            10000,
            "$('.overlay-trigger').length > 0"
        );

        $this->session->wait(
            15000,
            "$('.overlay-trigger').length <= 0"
        );

        $this->session->wait(
            10000,
            "$('.submitImportFile>span').is(':visible')"
        );
    }

    protected function getParsedTrsByStatus()
    {
        $result = array();
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $counterTr = count($tr);
        for ($k = 0; $k < $counterTr; $k++) {
            $td = $tr[$k]->findAll('css', 'td');
            $countedTd = count($td);
            if ($countedTd > 1 && !empty($td)) {
                $result[$td[0]->getHtml()][] = $tr[$k];
            }
        }

        return $result;
    }

    protected function fillSecondPageWrongValue()
    {
        $trs = $this->getParsedTrsByStatus();

        $this->assertNotNull(
            $finishAt = $trs['import.status.new'][1]->find('css', '.import_new_user_with_contract_contract_finishAt')
        );
        $finishAt->setValue('03/31/2014');

        $this->assertNotNull(
            $firstName = $trs['import.status.new'][2]->find('css', '.import_new_user_with_contract_tenant_first_name')
        );
        $firstName->setValue('Jung');

        $this->assertNotNull(
            $lastName = $trs['import.status.new'][1]->find('css', '.import_new_user_with_contract_tenant_last_name')
        );
        $lastName->setValue('Sophia');

        $this->assertNotNull(
            $finishAt = $trs['import.status.new'][3]->find('css', '.import_new_user_with_contract_contract_finishAt')
        );
        $finishAt->setValue('03/31/2014');

        $this->assertNotNull(
            $lastName = $trs['import.status.new'][4]->find('css', '.import_new_user_with_contract_tenant_last_name')
        );
        $lastName->setValue('Jr');
    }

    /**
     * @test
     */
    public function withoutPayment()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNotNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertEquals('error.file.empty', $error->getHtml());

        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import_failed.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNotNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertEquals('csv.file.too.small1', $error->getHtml());
        $this->assertNotNull($prev = $this->page->find('css', '.button'));
        $prev->click();
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->setProperty();
        $this->assertNotNull($dateSelector = $this->page->find('css', '.import-date'));
        $dateSelector->selectOption('m/d/Y');
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        for ($i = 1; $i <= 14; $i++) {
            $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
            if (isset($this->mapFile[$i])) {
                $choice->selectOption($this->mapFile[$i]);
            }
        }

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->session->wait(
            5000,
            "$('.errorField').length > 0"
        );
        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertEquals(2, count($errorFields));
        $this->assertEquals($errorFields[0]->getValue(), '2testmail.com');
        $this->assertEquals($errorFields[1]->getHtml(), 'tenant11@example.com');

        $trs = $this->getParsedTrsByStatus();

        $this->assertEquals(4, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.error']), "Error contract on first page is wrong number");
        $this->assertEquals(3, count($trs['import.status.new']), "New contract on first page is wrong number");
        $this->assertEquals(4, count($trs['import.status.skip']), "Skip contract on first page is wrong number");
        $this->assertEquals(1, count($trs['import.status.match']), "Match contract on first page is wrong number");
        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertEquals(2, count($errorFields));

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertEquals(1, count($errorFields));

        $this->assertNotNull(
            $email = $trs['import.status.new'][0]->find('css', '.import_new_user_with_contract_tenant_email')
        );
        $email->setValue('2test@mail.com');
        $submitImportFile->click();

        $this->waitReviewAndPost();

        $submitImportFile->click();

        $this->waitReviewAndPost();

        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertEquals(2, count($errorFields));
        $trs = $this->getParsedTrsByStatus();

        $this->assertEquals(2, count($trs), "Count statuses is wrong");
        $this->assertEquals(6, count($trs['import.status.new']), "New contract on first page is wrong number");
        $this->assertEquals(3, count($trs['import.status.skip']), "Skip contract on first page is wrong number");

        $this->fillSecondPageWrongValue();

        $submitImportFile->click();

        $this->session->wait(
            6000,
            "$('.finishedTitle').length > 0"
        );
        $submitImportFile->click();

        $this->session->wait(
            6000,
            "$('.finishedTitle').length > 0"
        );
        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());

        //Check notify tenant invite for new user
        $this->assertCount(10, $this->getEmails(), 'Wrong number of emails');
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => '2test@mail.com',
            )
        );
        $this->assertNotNull($tenant);
        $this->assertEquals($tenant->getFirstName(), 'Trent Direnna');
        $this->assertEquals($tenant->getLastName(), 'Jacquelyn Dacey');
        $this->assertEquals($tenant->getResidentsMapping()->first()->getResidentId(), 't0019851');

        // Check that first and last names are parsed correctly when field contains coma.
        $tenant2 = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => '19test@mail.com',
            )
        );
        $this->assertNotNull($tenant2);
        $this->assertEquals('Lisa Maria', $tenant2->getFirstName());
        $this->assertEquals('Sanders', $tenant2->getLastName());

        /**
         * @var $contract Contract
         */
        $contract = $tenant->getContracts()->first();
        /**
         * @var $Unit Unit
         */
        $unit = $contract->getUnit();
        $this->assertEquals('1017B', $unit->getName());
        $this->assertEquals(ContractStatus::INVITE, $contract->getStatus());
        $this->assertEquals('1200', $contract->getRent());
        $this->assertEquals('0', $contract->getIntegratedBalance());
        // startAt should be the first day of next month b/c this contract has no payments yet
        $this->assertEquals('11/09/2013', $contract->getStartAt()->format('m/d/Y'));
        $this->assertEquals('11/08/2015', $contract->getFinishAt()->format('m/d/Y'));

        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'tenant11@example.com',
            )
        );

        $contracts = $tenant->getContracts();
        $contractEnded = null;
        $contractMatch = null;
        foreach ($contracts as $contract) {
            if ($contract->getUnit()->getName() === '1-b') {
                $contractMatch = $contract;
                continue;
            }
        }

        //For match contract we don't need check startAt because it's not updated
        $this->assertEquals('1190', $contractMatch->getRent());
        $this->assertEquals('0', $contractMatch->getIntegratedBalance());
        $this->assertEquals('10/21/2016', $contractMatch->getFinishAt()->format('m/d/Y'));
        $this->assertEquals(ContractStatus::APPROVED, $contractMatch->getStatus());
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'hugo@rentrack.com',
            )
        );

        $contracts = $tenant->getContracts();
        $contractNew = null;
        foreach ($contracts as $contract) {
            if ($contract->getUnit()->getName() === '1017') {
                $contractNew = $contract;
                break;
            }
        }

        $this->assertEquals('950', $contractNew->getRent());
        $this->assertEquals('0', $contractNew->getIntegratedBalance());
        $this->assertEquals('03/18/2011', $contractNew->getStartAt()->format('m/d/Y'));
        $this->assertEquals('03/31/2015', $contractNew->getFinishAt()->format('m/d/Y'));
        $this->assertEquals(ContractStatus::APPROVED, $contractNew->getStatus());
    }

    /**
     * @test
     */
    public function withPayment()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $filePath = $this->getFilePathByName('import2.csv');
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->setProperty();
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));
        //Second step
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        for ($i = 1; $i <= 14; $i++) {
            $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
            if (isset($this->mapFile[$i])) {
                $choice->selectOption($this->mapFile[$i]);
            }
        }
        //Map Payment
        $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column15'));
        $choice->selectOption('payment_amount');
        $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column16'));
        $choice->selectOption('payment_date');
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->session->wait(
            5000,
            "$('.errorField').length > 0"
        );
        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertEquals(3, count($errorFields));
        //Make sure for this page we don't have operation
        $this->assertNull($errorField = $this->page->find('css', '.import_operation_paidFor'));

        $trs = $this->getParsedTrsByStatus();
        $this->assertNotNull(
            $email = $trs['import.status.new'][0]->find('css', '.import_new_user_with_contract_tenant_email')
        );
        $email->setValue('2test@mail.com');
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        //Make sure for this page we have operation
        $today = new DateTime();
        $this->assertNotNull($paidFor = $this->page->findAll('css', '.import_operation_paidFor'));
        $this->assertEquals(1, count($paidFor));
        $paidFor[0]->setValue($today->format('m/d/Y'));

        $this->assertNotNull($amount = $this->page->findAll('css', '.import_operation_amount'));
        $this->assertEquals(1, count($amount));
        $amount[0]->setValue('99.99');

        $this->fillSecondPageWrongValue();

        $submitImportFile->click();
        $this->waitReviewAndPost();

        $this->session->wait(
            5000,
            "$('.finishedTitle').length > 0"
        );

        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());

        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'marion@rentrack.com',
            )
        );
        $this->assertNotNull($tenant);
        /**
         * @var $operation Operation
         */
        $operation = $em->getRepository('DataBundle:Operation')->findOneBy(
            array(
                'amount' => '99.99',
                'paidFor'=> $today,
            )
        );

        $this->assertNotNull($operation);
        $user = $operation->getOrder()->getUser();
        $this->assertNotNull($user);
        $this->assertEquals($tenant->getEmail(), $user->getEmail());
    }

    protected function getWaitingRoom()
    {
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findBy(
            array(
                'residentId' => 't0019851',
            )
        );

        $this->assertNotNull($contractWaiting);
        $this->assertEquals(1, count($contractWaiting));
        /**
         * @var $contractWaiting ContractWaiting
         */
        $contractWaiting = reset($contractWaiting);
        $this->assertNotNull($contractWaiting);

        return $contractWaiting;
    }

    /**
     * @test
     */
    public function waitingRoom()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        for ($i = 0; $i < 2; $i++) {
            $this->login('landlord1@example.com', 'pass');
            $this->page->clickLink('tab.accounting');
            //First Step
            $this->session->wait(5000, "typeof jQuery != 'undefined'");
            $filePath = $this->getFilePathByName('import_waiting_room.csv');
            $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
            $attFile->attachFile($filePath);
            $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
            $this->setProperty();
            $submitImportFile->click();
            $this->assertNull($error = $this->page->find('css', '.error_list>li'));
            $this->assertNotNull($table = $this->page->find('css', 'table'));
            //Second step
            $this->assertNotNull($table = $this->page->find('css', 'table'));

            for ($i = 1; $i <= 14; $i++) {
                $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
                if (isset($this->mapFile[$i])) {
                    $choice->selectOption($this->mapFile[$i]);
                }
            }
            //Map Payment
            $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
            $submitImportFile->click();
            $this->session->wait(
                5000,
                "$('#importTable').length > 0 && $('#importTable').is(':visible')"
            );
            $trs = $this->getParsedTrsByStatus();

            $this->assertEquals(2, count($trs), "Count statuses is wrong");
            $this->assertEquals(
                count($trs['import.status.waiting']),
                1,
                "Waiting contract on first page is wrong number ".$i
            );
            $this->assertEquals(
                1,
                count($trs['import.status.skip']),
                "Skip contract on first page is wrong number ".$i
            );

            $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
            $submitImportFile->click();
            $this->session->wait(
                5000,
                "$('.finishedTitle').length > 0"
            );

            $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
            $this->assertEquals('import.review.finish', $finishedTitle->getHtml());
            $this->logout();
        }

        $this->getWaitingRoom();
    }

    /**
     * @depends waitingRoom
     * @test
     */
    public function createContractFromWaiting()
    {
        $contractWaiting = $this->getWaitingRoom();
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $property Property
         */
        $property = $em->getRepository('RjDataBundle:Property')->findOneBy(
            array(
                'jb' => '40.7308443',
                'kb' => '-73.9913642',
            )
        );

        $this->assertNotNull($property);

        $this->session->visit($this->getUrl() . 'user/new/'.$property->getId());
        $this->assertNotNull($thisIsMyRental = $this->page->find('css', '.thisIsMyRental'));
        $thisIsMyRental->click();
        $this->assertNotNull($form = $this->page->find('css', '#formNewUser'));
        $this->fillForm(
            $form,
            array(
                'rentjeeves_publicbundle_tenanttype_first_name'                => $contractWaiting->getFirstName().'Wr',
                'rentjeeves_publicbundle_tenanttype_last_name'                 => $contractWaiting->getLastName(),
                'rentjeeves_publicbundle_tenanttype_email'                     => 'hi@mail.com',
                'rentjeeves_publicbundle_tenanttype_password_Password'         => 'pass',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password'  => 'pass',
                'rentjeeves_publicbundle_tenanttype_tos'                       => true,
            )
        );

        $this->assertNotNull($selectUnit = $this->page->find('css', '.select-unit'));
        $selectUnit->selectOption($contractWaiting->getUnit()->getName());

        $this->assertNotNull($submit = $this->page->find('css', '#register'));
        $submit->click();
        $this->assertNotNull($unitReserved = $this->page->find('css', '.error_list>li'));
        $this->assertEquals('error.unit.reserved', $unitReserved->getHtml());

        $this->assertNotNull($form = $this->page->find('css', '#formNewUser'));
        $this->fillForm(
            $form,
            array(
                'rentjeeves_publicbundle_tenanttype_first_name'                => $contractWaiting->getFirstName(),
                'rentjeeves_publicbundle_tenanttype_last_name'                 => $contractWaiting->getLastName(),
                'rentjeeves_publicbundle_tenanttype_email'                     => 'hi@mail.com',
                'rentjeeves_publicbundle_tenanttype_password_Password'         => 'pass',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password'  => 'pass',
                'rentjeeves_publicbundle_tenanttype_tos'                       => true,
            )
        );

        $this->assertNotNull($submit = $this->page->find('css', '#register'));
        $submit->click();
        $fields = $this->page->findAll('css', '#inviteText>h4');
        $this->assertCount(2, $fields, 'wrong number of text h4');

        //Check contract

        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'hi@mail.com',
            )
        );
        $this->assertNotNull($tenant);
        $contracts = $tenant->getContracts();
        $this->assertEquals(1, count($contracts));
        /**
         * @var $contract Contract
         */
        $contract = $contracts[0];
        $this->assertEquals($contract->getStartAt(), $contractWaiting->getStartAt());
        $this->assertEquals($contract->getFinishAt(), $contractWaiting->getFinishAt());
        $this->assertEquals($contract->getRent(), $contractWaiting->getRent());
        $this->assertEquals($contract->getIntegratedBalance(), $contractWaiting->getIntegratedBalance());
        $this->assertEquals($contract->getStartAt(), $contractWaiting->getStartAt());
        $this->assertEquals($contract->getUnit()->getId(), $contractWaiting->getUnit()->getId());

        $mapping = $tenant->getResidentsMapping();
        $this->assertEquals(1, count($mapping));
        /**
         * @var $mapping ResidentMapping
         */
        $mapping = $mapping[0];
        $this->assertEquals($mapping->getResidentId(), $contractWaiting->getResidentId());
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->find($contractWaiting->getId());
        $this->assertNotNull($contractWaiting);
    }

    /**
     * @depends createContractFromWaiting
     * @test
     */
    public function checkFindingUserByResidentId()
    {
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'hi@mail.com',
            )
        );

        $tenant->setEmail('h1_changed@mail.com');
        $em->persist($tenant);
        $em->flush();

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $filePath = $this->getFilePathByName('import_waiting_room.csv');
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $attFile->attachFile($filePath);
        $this->setProperty();
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));
        //Second step
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        for ($i = 1; $i <= 14; $i++) {
            $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
            if (isset($this->mapFile[$i])) {
                $choice->selectOption($this->mapFile[$i]);
            }
        }
        //Map Payment
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->session->wait(
            5000,
            "$('#importTable').length > 0 && $('#importTable').is(':visible')"
        );
        $trs = $this->getParsedTrsByStatus();

        $this->assertEquals(2, count($trs), "Count statuses is wrong");
        $this->assertEquals(
            count($trs['import.status.match']),
            1,
            "Match contract on first page has wrong count"
        );
        $this->assertEquals(1, count($trs['import.status.skip']), "Skip contract on first page is wrong number");

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->session->wait(
            5000,
            "$('.finishedTitle').length > 0"
        );

        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());
        $this->logout();
    }

    /**
     * @test
     */
    public function checkFormatDate()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $filePath = $this->getFilePathByName('import_date_format.csv');
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $this->setProperty();
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->assertNotNull($dateFormat = $this->page->find('css', '#import_file_type_dateFormat'));
        $dateFormat->selectOption('n-j-Y');
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));
        //Second step
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        for ($i = 1; $i <= 14; $i++) {
            $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
            if (isset($this->mapFile[$i])) {
                $choice->selectOption($this->mapFile[$i]);
            }
        }
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->session->wait(
            5000,
            "$('#importTable').length > 0 && $('#importTable').is(':visible')"
        );

        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $counterTr = count($tr);
        $result = array();
        for ($k = 0; $k < $counterTr; $k++) {
            $td = $tr[$k]->findAll('css', 'td');
            $countedTd = count($td);
            if ($countedTd > 1 && !empty($td)) {
                $result[] = $tr[$k];
            }
        }

        $this->assertEquals(2, count($result));
        $td = $result[0]->findAll('css', 'td');

        $this->assertEquals('12/29/2012<br>3/1/2013', $td[7]->getHtml(), $td[7]->getHtml());
        $datepicker = $result[1]->findAll('css', '.datepicker');
        $this->assertEquals(2, count($datepicker));
        $this->assertEquals('11/09/2013', $datepicker[0]->getValue(), $datepicker[0]->getValue());
        $this->assertEquals('11/08/2025', $datepicker[1]->getValue(), $datepicker[1]->getValue());
        $this->logout();
    }

    protected function setProperty()
    {
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $property = $em->getRepository('RjDataBundle:Property')->findOneBy(
            array(
                'street' => 'Broadway',
                'number' => '770',
                'zip'    => '10003'
            )
        );
        $this->assertNotNull($propertySelector = $this->page->find('css', '#import_file_type_property'));
        $propertySelector->selectOption($property->getId());
    }

    /**
     * @test
     */
    public function importMultipleProperties()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->session->wait(5000, "typeof jQuery != 'undefined'");

        //First Step
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import_multiple.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->assertNotNull($dateSelector = $this->page->find('css', '.import-date'));
        $dateSelector->selectOption('m/d/y');
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        for ($i = 1; $i <= 17; $i++) {
            $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
            if (isset($this->mapMultiplePropertyFile[$i])) {
                $choice->selectOption($this->mapMultiplePropertyFile[$i]);
            }
        }

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->session->wait(
            5000,
            "$('.errorField').length > 0"
        );
        $this->waitReviewAndPost();
        $this->assertNotNull($errorFields = $this->page->findAll('css', 'input.errorField'));
        $this->assertEquals(5, count($errorFields));

        $this->assertEquals('', $errorFields[0]->getValue());
        $this->assertEquals('', $errorFields[1]->getValue());
        $this->assertEquals('& Adelai', $errorFields[2]->getValue());
        $this->assertEquals('Carol Acha.Mo', $errorFields[3]->getValue());
        $this->assertEquals('Matthew &', $errorFields[4]->getValue());

        $trs = $this->getParsedTrsByStatus();

        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        $this->assertEquals(9, count($trs['import.status.waiting']), "All contracts should be waiting");


        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));

        $this->assertNotNull($firstName1 = $this->page->find('css', 'input.1_first_name'));
        $firstName1->setValue('Logan');
        $this->assertNotNull($lastName1 = $this->page->find('css', 'input.1_last_name'));
        $lastName1->setValue('Cooper');

        $this->assertNotNull($lastName2 = $this->page->find('css', 'input.2_last_name'));
        $lastName2->setValue('Adelai');

        $this->assertNotNull($lastName3 = $this->page->find('css', '.3_last_name'));
        $lastName3->setValue('AchaMo');

        $this->assertNotNull($lastName4 = $this->page->find('css', 'input.4_first_name'));
        $lastName4->setValue('Matthew');

        $submitImportFile->click();

        $this->waitReviewAndPost();

        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(9, count($trs['import.status.waiting']), "All contracts should be waiting");

        $this->assertNotNull($errorFields = $this->page->findAll('css', 'input.errorField'));
        $this->assertEquals(0, count($errorFields));
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));

        $submitImportFile->click();

        $this->session->wait(
            $this->timeout,
            "jQuery('.overlay-trigger').length > 0"
        );

        $this->session->wait(
            $this->timeout,
            "jQuery('.overlay-trigger').length = 0"
        );

        $submitImportFile->click();

        $this->session->wait(
            $this->timeout,
            "jQuery('.overlay-trigger').length > 0"
        );
        $this->session->wait(
            $this->timeout,
            "jQuery('.overlay-trigger').length = 0"
        );

        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());

        //Check notify tenant invite for new user
        $this->assertCount(0, $this->getEmails(), 'Wrong number of emails');
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $unitMapping = $em->getRepository('RjDataBundle:UnitMapping')->findOneBy(
            array('externalUnitId' => 'SP1152-C')
        );
        $this->assertNotNull($unitMapping);
        $this->assertNotNull($unit = $unitMapping->getUnit());

        /** @var ContractWaiting $waitingContract */
        $waitingContract = $em->getRepository('RjDataBundle:ContractWaiting')->findOneBy(
            array(
                'residentId'    => 'ABBOTT,MIT',
                'firstName'     => 'Logan',
                'lastName'      => 'Cooper',
                'property'      => $unit->getProperty()->getId(),
                'unit'          => $unit->getId(),
            )
        );
        $this->assertNotNull($waitingContract);
        $this->assertEquals(975, $waitingContract->getRent());
        $this->assertEquals(193, $waitingContract->getIntegratedBalance());
        $this->assertEquals('2014-01-01', $waitingContract->getStartAt()->format('Y-m-d'));
        $this->assertEquals('2015-01-31', $waitingContract->getFinishAt()->format('Y-m-d'));
        $this->assertTrue($unit->getProperty()->isSingle());

        $this->assertEquals(20, count($em->getRepository('RjDataBundle:ContractWaiting')->findAll()));
    }

    /**
     * @test
     * @depends importMultipleProperties
     */
    public function signUpFromImportedWaitingContract()
    {
        $this->markTestSkipped('Temporarily skip this test due to: PHP Fatal error:  Allowed memory size exhausted');
        $this->setDefaultSession('selenium2');
        $this->logout();
        $em = $this->getContainer()->get('doctrine')->getManager();

        $unitMapping = $em->getRepository('RjDataBundle:UnitMapping')->findOneBy(
            array('externalUnitId' => 'NO813-C')
        );
        $this->assertNotNull($unitMapping);
        $this->assertNotNull($unit = $unitMapping->getUnit());

        $waitingContractParams = array(
            'residentId' => 'ALDRICH,JOSHUA',
            'firstName' => 'Daniel',
            'lastName' => 'Price',
            'property' => $unit->getProperty()->getId(),
            'unit' => $unit->getId(),
        );
        /** @var ContractWaiting $waitingContract */
        $waitingContract = $em->getRepository('RjDataBundle:ContractWaiting')->findOneBy(
            $waitingContractParams
        );
        $this->assertNotNull($waitingContract);

        $this->session->visit($this->getUrl() . 'iframe');
        $this->session->wait($this->timeout, "typeof $ !== undefined");

        $address = '813 West Nopal Place, Chandler, AZ 85225';
        $this->assertNotNull($form = $this->page->find('css', '#formSearch'));
        $this->assertNotNull($propertySearch = $this->page->find('css', '#property-add'));
        $this->fillForm(
            $form,
            array(
                'property-search' => $address,
            )
        );
        $propertySearch->click();
        $this->session->wait($this->timeout, "window.location.pathname.match('\/user\/new\/[0-9]') != null");
        $this->session->wait($this->timeout, "$('#register').length > 0");

        $this->assertNotNull($form = $this->page->find('css', '#formNewUser'));
        $this->fillForm(
            $form,
            array(
                'rentjeeves_publicbundle_tenanttype_first_name'                => "Daniel",
                'rentjeeves_publicbundle_tenanttype_last_name'                 => "Price",
                'rentjeeves_publicbundle_tenanttype_email'                     => 'dan.price@mail.com',
                'rentjeeves_publicbundle_tenanttype_password_Password'         => 'pass',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password'  => 'pass',
                'rentjeeves_publicbundle_tenanttype_tos'                       => true,
            )
        );
        $this->assertNotNull($thisIsMyRental = $this->page->find('css', '.thisIsMyRental'));
        $thisIsMyRental->click();
        $this->assertNotNull($submit = $this->page->find('css', '#register'));
        $submit->click();

        $waitingContract = $em->getRepository('RjDataBundle:ContractWaiting')->findOneBy(
            $waitingContractParams
        );

        $this->assertNull($waitingContract);
        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array('email' => 'dan.price@mail.com')
        );
        $this->assertNotNull($tenant);
        $this->assertEquals('Daniel', $tenant->getFirstName());
        $this->assertEquals('Price', $tenant->getLastName());
    }

    /**
     * @test
     */
    public function alreadyHaveAccount()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import_one_user.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->setProperty();
        $this->assertNotNull($dateSelector = $this->page->find('css', '.import-date'));
        $dateSelector->selectOption('m/d/Y');
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        for ($i = 1; $i <= 14; $i++) {
            $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
            if (isset($this->mapFile[$i])) {
                $choice->selectOption($this->mapFile[$i]);
            }
        }

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->session->wait(
            $this->timeout,
            "$('.errorField').length > 0"
        );

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->session->wait(
            $this->timeout,
            "$('.finishedTitle').length > 0"
        );

        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());
        $this->logout();
        //Check notify tenant invite for new user
        $this->setDefaultSession('goutte');
        $emails = $this->getEmails();
        $this->assertCount(1, $emails, 'Wrong number of emails');
        $email = $this->getEmailReader()->getEmail(array_pop($emails))->getMessage('text/html');
        $crawler = $this->getCrawlerObject($email->getBody());
        $url = $crawler->filter('#payRentLink')->getNode(0)->getAttribute('href');

        $this->session->visit($url);
        $this->assertNotNull($haveAccount = $this->page->find('css', '.haveAccount>a'));
        $haveAccount->click();

        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($payButtons = $this->page->findAll('css', '.button-contract-pay'));
        $this->assertCount(5, $payButtons);
        $this->logout();

        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            array(
                'rent'               => 777666.00,
                'integratedBalance'  => 1277.00
            )
        );

        $this->assertNotNull($contract);
        $this->assertEquals('tenant11@example.com', $contract->getTenant()->getEmail());
    }

    /**
     * @test
     * @depends alreadyHaveAccount
     */
    public function checkMutchedUser()
    {
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import_one_user.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->setProperty();
        $this->assertNotNull($dateSelector = $this->page->find('css', '.import-date'));
        $dateSelector->selectOption('m/d/Y');
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        for ($i = 1; $i <= 14; $i++) {
            $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
            if (isset($this->mapFile[$i])) {
                $choice->selectOption($this->mapFile[$i]);
            }
        }

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.match']), "Match contract is wrong number");
    }

    /**
     * @test
     */
    public function matchWaitingContract()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('duplicate_waiting_room.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $mapFile = array(
            '1' => ImportMapping::KEY_RESIDENT_ID,
            '2' => ImportMapping::KEY_TENANT_NAME,
            '3' => ImportMapping::KEY_RENT,
            '4' => ImportMapping::KEY_BALANCE,
            '5' => ImportMapping::KEY_UNIT_ID,
            '6' => ImportMapping::KEY_STREET,
            '7' => ImportMapping::KEY_CITY,
            '8' => ImportMapping::KEY_STATE,
            '9' => ImportMapping::KEY_ZIP,
            '10'=> ImportMapping::KEY_MOVE_IN,
            '11'=> ImportMapping::KEY_LEASE_END,
            '12'=> ImportMapping::KEY_MOVE_OUT,
            '14'=> ImportMapping::KEY_EMAIL,
        );
        for ($i = 1; $i <= 15; $i++) {
            if (isset($mapFile[$i])) {
                $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
                $choice->selectOption($mapFile[$i]);
            }
        }

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.waiting']), "Waiting contract is wrong number");
        $this->assertNotNull($firstName1 = $this->page->find('css', 'input.0_first_name'));
        $firstName1->setValue('Logan');
        $this->assertNotNull($lastName1 = $this->page->find('css', 'input.0_last_name'));
        $lastName1->setValue('Cooper');
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->session->wait(
            5000,
            "$('.finishedTitle').length > 0"
        );

        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());
        //after that check mathced status
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('duplicate_waiting_room.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        for ($i = 1; $i <= 15; $i++) {
            if (isset($mapFile[$i])) {
                $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
                $choice->selectOption($mapFile[$i]);
            }
        }

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.match']), "Match contract is wrong number");
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->session->wait(
            6000,
            "$('.finishedTitle').length > 0"
        );

        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());
    }

    /**
     * @test
     */
    public function matchWaitingContractWithMoveContract()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('one_user_for_waiting_room.csv');
        $attFile->attachFile($filePath);
        $this->setProperty();
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $mapFile = array(
            '1' => ImportMapping::KEY_UNIT,
            '2' => ImportMapping::KEY_RESIDENT_ID,
            '3' => ImportMapping::KEY_TENANT_NAME,
            '4' => ImportMapping::KEY_RENT,
            '5' => ImportMapping::KEY_MOVE_IN,
            '6' => ImportMapping::KEY_LEASE_END,
            '7' => ImportMapping::KEY_MOVE_OUT,
            '8' => ImportMapping::KEY_BALANCE,
            '9' => ImportMapping::KEY_EMAIL,
        );
        for ($i = 1; $i <= 15; $i++) {
            if (isset($mapFile[$i])) {
                $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
                $choice->selectOption($mapFile[$i]);
            }
        }

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.new']), "New contract is wrong number");
        $this->assertNotNull($email = $this->page->find('css', 'input.0_email'));
        $email->setValue('');
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->session->wait(
            5000,
            "$('.finishedTitle').length > 0"
        );

        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());
        //after that check mathced status
        $this->page->clickLink('tab.accounting');

        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('one_user_for_waiting_room.csv');
        $attFile->attachFile($filePath);
        $this->setProperty();
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $mapFile = array(
            '1' => ImportMapping::KEY_UNIT,
            '2' => ImportMapping::KEY_RESIDENT_ID,
            '3' => ImportMapping::KEY_TENANT_NAME,
            '4' => ImportMapping::KEY_RENT,
            '5' => ImportMapping::KEY_MOVE_IN,
            '6' => ImportMapping::KEY_LEASE_END,
            '7' => ImportMapping::KEY_MOVE_OUT,
            '8' => ImportMapping::KEY_BALANCE,
            '9' => ImportMapping::KEY_EMAIL,
        );
        for ($i = 1; $i <= 15; $i++) {
            if (isset($mapFile[$i])) {
                $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
                $choice->selectOption($mapFile[$i]);
            }
        }

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.match']), "Match contract is wrong number");
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->session->wait(
            5000,
            "$('.finishedTitle').length > 0"
        );

        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());


        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $contracts = $em->getRepository('RjDataBundle:Contract')->findBy(
            array(
                'integratedBalance' => '-29.80',
            )
        );
        $this->assertEquals(1, count($contracts));
    }

    /**
     * @test
     */
    public function duplicateResidentIdShouldBeSkippedWithError()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import_two_user.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->setProperty();
        $this->assertNotNull($dateSelector = $this->page->find('css', '.import-date'));
        $dateSelector->selectOption('m/d/Y');
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        for ($i = 1; $i <= 14; $i++) {
            $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
            if (isset($this->mapFile[$i])) {
                $choice->selectOption($this->mapFile[$i]);
            }
        }

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->session->wait(
            5000,
            "$('.errorField').length > 0"
        );
        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertEquals(2, count($errorFields));
        $this->assertEquals($errorFields[1]->getHtml(), '14test@mail.com');
        $this->assertEquals(
            trim($errorFields[0]->getHtml()),
            '<span data-bind="text:resident_mapping.resident_id">t0016437</span>'
        );
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->session->wait(
            5000,
            "$('.finishedTitle').length > 0"
        );

        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $contracts = $em->getRepository('RjDataBundle:Contract')->findBy(
            array(
                'rent' => 777666,
            )
        );
        $this->assertEquals(1, count($contracts));
        $this->logout();
    }

    /**
     * @test
     */
    public function yardiBaseImport()
    {
        $this->setDefaultSession('selenium2');

        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            array(
                'yardiPaymentAccepted' => YardiPaymentAccepted::CASH_EQUIVALENT,
            )
        );
        $this->assertEquals(0, count($contract));
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findOneBy(
            array(
                'yardiPaymentAccepted' => YardiPaymentAccepted::DO_NOT_ACCEPT,
            )
        );
        $this->assertEquals(0, count($contractWaiting));

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($submitImport = $this->page->find('css', '.submitImportFile'));
        $this->setProperty();
        $this->assertNotNull($yardiRadio = $this->page->findAll('css', '.radio'));
        $yardiRadio[1]->click();
        $this->assertNotNull($propertyId = $this->page->find('css', '#import_file_type_propertyId'));
        $propertyId->setValue('rnttrk01');
        $submitImport->click();

        $this->session->wait(
            80000,
            "$('table').is(':visible')"
        );
        $this->waitReviewAndPost();
        for ($i = 0; $i <= 3; $i++) {
            $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
            $submitImportFile->click();
            $this->waitReviewAndPost();
        }

        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());
        $this->logout();

        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            array(
                'yardiPaymentAccepted' => YardiPaymentAccepted::CASH_EQUIVALENT,
            )
        );
        $this->assertEquals(1, count($contract));
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findOneBy(
            array(
                'yardiPaymentAccepted' => YardiPaymentAccepted::DO_NOT_ACCEPT,
            )
        );
        $this->assertEquals(1, count($contractWaiting));
    }

    /**
     * @test
     * @depends yardiBaseImport
     */
    public function yardiBaseImportOnlyException()
    {
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($submitImport = $this->page->find('css', '.submitImportFile'));
        $this->setProperty();
        $this->assertNotNull($yardiRadio = $this->page->findAll('css', '.radio'));
        $yardiRadio[1]->click();
        $this->assertNotNull($propertyId = $this->page->find('css', '#import_file_type_propertyId'));
        $propertyId->setValue('rnttrk01');
        $this->assertNotNull($exceptionOnly = $this->page->find('css', '#import_file_type_onlyException'));
        $exceptionOnly->check();

        $submitImport->click();

        $this->session->wait(
            80000,
            "$('table').is(':visible')"
        );
        $this->waitReviewAndPost();
        $this->session->wait(
            5000,
            "$('.finishedTitle').length > 0"
        );

        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());
    }

    /**
     * @test
     */
    public function skippedMessageAndinfoDateInvalid()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('skipped_message_and_date_notice.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $mapFile = array(
            '1' => ImportMapping::KEY_RESIDENT_ID,
            '2' => ImportMapping::KEY_TENANT_NAME,
            '3' => ImportMapping::KEY_RENT,
            '4' => ImportMapping::KEY_BALANCE,
            '5' => ImportMapping::KEY_UNIT_ID,
            '6' => ImportMapping::KEY_STREET,
            '7' => ImportMapping::KEY_CITY,
            '8' => ImportMapping::KEY_STATE,
            '9' => ImportMapping::KEY_ZIP,
            '10'=> ImportMapping::KEY_MOVE_IN,
            '11'=> ImportMapping::KEY_LEASE_END,
            '12'=> ImportMapping::KEY_MOVE_OUT,
            '14'=> ImportMapping::KEY_EMAIL,
        );
        for ($i = 1; $i <= 15; $i++) {
            if (isset($mapFile[$i])) {
                $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
                $choice->selectOption($mapFile[$i]);
            }
        }

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(2, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.waiting']), "Waiting contract is wrong number");
        $this->assertEquals(1, count($trs['import.status.skip']), "Waiting contract is wrong number");

        $this->assertNotNull($info = $this->page->find('css', '.information-box'));
        $this->assertEquals('import.error.mapping_date', trim($info->getHtml()));
        $this->assertNotNull($td = $this->page->find('css', '.line_number_1 td'));
        $this->assertEquals('import.info.skipped2', trim($td->getAttribute('title')));
    }

    /**
     * @test
     */
    public function setsMatchingFieldsForImport()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');

        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $filePath = $this->getFilePathByName('import2.csv');
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->setProperty();
        $submitImportFile->click();
        // Choose mapping fields
        $this->assertNotNull($table = $this->page->find('css', 'table'));
        for ($i = 1; $i <= 14; $i++) {
            $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
            $this->assertEquals('empty_value', $choice->getValue());
            if (isset($this->mapFile[$i])) {
                $choice->selectOption($this->mapFile[$i]);
            }
        }

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNotNull($table = $this->page->find('css', 'table#importTable'));
        $this->logout();
        // Check that mapping is saved to DB
        $importMappingChoice = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('RjDataBundle:ImportMappingChoice')->findAll();
        $this->assertTrue(count($importMappingChoice) > 0);

        // Do import again w/o mapping
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $filePath = $this->getFilePathByName('import2.csv');
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->setProperty();
        $submitImportFile->click();
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNotNull($table = $this->page->find('css', 'table#importTable'));
        $this->logout();
    }

    public function providerForShouldCreateOperation()
    {
        return array(
            array($isFirstRunTest = true),
            array($isFirstRunTest = false),
        );
    }


    /**
     * @test
     * @dataProvider providerForShouldCreateOperation
     */
    public function shouldCreateOperation($isFirstRunTest)
    {
        if ($isFirstRunTest) {
            $this->load(true);
        }
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import_should_create_operation.csv');
        $attFile->attachFile($filePath);
        $this->setProperty();
        $this->assertNotNull($dateSelector = $this->page->find('css', '.import-date'));
        $dateSelector->selectOption('m/d/Y');
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        for ($i = 1; $i <= 14; $i++) {
            $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
            if (isset($this->mapFile[$i])) {
                $choice->selectOption($this->mapFile[$i]);
            }
        }

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();

        $trs = $this->getParsedTrsByStatus();

        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        if ($isFirstRunTest) {
            $this->assertEquals(1, count($trs['import.status.new']), "New contract on first page is wrong number");
        } else {
            $this->assertEquals(1, count($trs['import.status.match']), "Match contract on first page is wrong number");
        }
        $this->assertEmpty($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $this->session->wait(
            6000,
            "$('.finishedTitle').length > 0"
        );
        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => '14test@mail.com',
            )
        );
        /**
         * @var $contract Contract
         */
        $this->assertNotNull($contract = $tenant->getContracts()->first());
        if ($isFirstRunTest) {
            $contract->setIntegratedBalance(0);
            $contract->setStatus(ContractStatus::CURRENT);
            $paidTo = new DateTime();
            $paidTo->modify("-5 days");
            $contract->setPaidTo($paidTo);
            $em->flush($contract);
        } else {
            $this->assertEquals(count($operations = $contract->getOperations()), 1);
        }
    }
}
