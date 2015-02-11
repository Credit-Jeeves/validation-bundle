<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\AccountingSettings;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\DataBundle\Model\Unit;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as ImportMapping;
use RentJeeves\LandlordBundle\Form\Enum\ImportType;
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

    protected $mapMultiplePropertyFile = [
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
    ];

    protected $mapMultipleGroupFile = [
        '1' => ImportMapping::KEY_GROUP_ACCOUNT_NUMBER,
        '2' => ImportMapping::KEY_RESIDENT_ID,
        '3' => ImportMapping::KEY_TENANT_NAME,
        '4' => ImportMapping::KEY_RENT,
        '5' => ImportMapping::KEY_BALANCE,
        '6' => ImportMapping::KEY_UNIT_ID,
        '7' => ImportMapping::KEY_STREET,
        '9' => ImportMapping::KEY_UNIT,
        '10' => ImportMapping::KEY_CITY,
        '11'=> ImportMapping::KEY_STATE,
        '12'=> ImportMapping::KEY_ZIP,
        '14'=> ImportMapping::KEY_MOVE_IN,
        '15'=> ImportMapping::KEY_LEASE_END,
        '16'=> ImportMapping::KEY_MOVE_OUT,
        '18'=> ImportMapping::KEY_EMAIL,
    ];

    protected function getFilePathByName($fileName)
    {
        $sep = DIRECTORY_SEPARATOR;
        $filePath = getcwd();
        $filePath .= $sep.'data'.$sep.'fixtures'.$sep.$fileName;
        return $filePath;
    }

    protected function waitReviewAndPost($waitSubmit = true)
    {
        $this->session->wait(
            4000,
            "$('.overlay-trigger').length > 0"
        );

        $this->session->wait(
            21000,
            "$('.overlay-trigger').length <= 0"
        );

        if ($waitSubmit == true) {
            $this->session->wait(
                10000,
                "$('.submitImportFile>span').is(':visible')"
            );
        }
    }

    protected function getParsedTrsByStatus()
    {
        $result = [];
        $tds = $this->page->findAll(
            'css',
            '#importTable>tbody>tr>td.import_status_text'
        );

        foreach ($tds as $td) {
            $result[$td->getHtml()][] = $td->getParent();
        }

        return $result;
    }

    protected function fillSecondPageWrongValue($trs)
    {

        $this->assertNotNull(
            $firstName = $trs['import.status.new'][0]->find('css', '.import_new_user_with_contract_tenant_first_name')
        );
        $firstName->setValue('Jung');

        $this->assertNotNull(
            $lastName = $trs['import.status.new'][0]->find('css', '.import_new_user_with_contract_tenant_last_name')
        );
        $lastName->setValue('Sophia');

        $this->assertNotNull(
            $lastName = $trs['import.status.new'][1]->find('css', '.import_new_user_with_contract_tenant_last_name')
        );
        $lastName->setValue('Jr');
    }

    /**
     * @test
     */
    public function shouldGetMappingForImport()
    {
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $group = $em->getRepository('DataBundle:Group')->findOneBy(
            array(
                'name' => 'Test Rent Group'
            )
        );
        $externalUnitId = 'AAABBB-7';
        $unitMapping = $em->getRepository('RjDataBundle:UnitMapping')->getMappingForImport(
            $group,
            $externalUnitId
        );

        $this->assertNotNull($unitMapping);
    }

    /**
     * @test
     */
    public function shouldImportFile()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list li'));
        $this->assertCount(2, $errors, 'Wrong number of errors');
        $this->assertEquals('import.errors.single_property_select', $errors[0]->getHtml());
        $this->assertEquals('error.file.empty', $errors[1]->getHtml());

        $this->setPropertyFirst();
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
        $this->setPropertyFirst();
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
        $this->assertCount(2, $errorFields);
        $this->assertEquals($errorFields[0]->getValue(), '2testmail.com');
        $this->assertEquals($errorFields[1]->getHtml(), 'tenant11@example.com');

        $trs = $this->getParsedTrsByStatus();

        $this->assertCount(4, $trs, "Count statuses is wrong");
        $this->assertCount(1, $trs['import.status.error'], "Count contract with status 'error' wrong");
        $this->assertCount(3, $trs['import.status.new'], "Count contract with status 'new' wrong");
        $this->assertCount(4, $trs['import.status.skip'], "Count contract with status 'skip' wrong");
        $this->assertCount(1, $trs['import.status.match'], "Count contract with status 'match' wrong");
        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertCount(2, $errorFields);

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertCount(1, $errorFields);

        $trs = $this->getParsedTrsByStatus();
        $this->assertCount(1, $trs, "Count statuses of contract is wrong");

        $this->assertNotNull(
            $email = $trs['import.status.new'][0]->find('css', '.import_new_user_with_contract_tenant_email')
        );
        $email->setValue('2test@mail.com');

        $submitImportFile->click();
        $this->waitReviewAndPost();

        $trs = $this->getParsedTrsByStatus();
        $this->assertCount(2, $trs, "Count statuses is wrong");
        $this->assertCount(
            6,
            $trs['import.status.new'],
            "Count contracts with status 'new' is wrong. On first page."
        );
        $this->assertCount(
            3,
            $trs['import.status.skip'],
            "Count contracts with status 'skip' is wrong. On first page."
        );
        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertCount(2, $errorFields);

        $submitImportFile->click();
        $this->waitReviewAndPost();

        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertCount(2, $errorFields);
        $trs = $this->getParsedTrsByStatus();

        $this->assertCount(1, $trs, "Count contract wrong");
        $this->assertCount(2, $trs['import.status.new'], "Count contracts with status 'new' is wrong.");

        $this->fillSecondPageWrongValue($trs);

        $submitImportFile->click();

        $this->session->wait(
            6000,
            "$('.finishedTitle').length > 0"
        );
        $submitImportFile->click();

        $this->session->wait(
            10000,
            "$('.finishedTitle').length > 0"
        );
        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());

        //Check notify tenant invite for new user or update his contract rent
        $this->assertCount(9, $this->getEmails(), 'Wrong number of emails');
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
            $this->setPropertyFirst();
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
                9000,
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
                'jb' => '40.7307693',
                'kb' => '-73.9913223',
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
        $this->setPropertyFirst();
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
            8000,
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
        $this->setPropertyFirst();
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

    protected function setPropertyFirst()
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

    protected function setPropertySecond()
    {
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $property = $em->getRepository('RjDataBundle:Property')->findOneBy(
            array(
                'street' => 'Broadway',
                'number' => '785',
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
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $this->assertEquals(1, count($em->getRepository('RjDataBundle:ContractWaiting')->findAll()));
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->session->wait(5000, "typeof jQuery != 'undefined'");

        //First Step
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $this->assertNotNull($importTypeSelected = $this->page->find('css', '#import_file_type_importType'));
        $importTypeSelected->selectOption(ImportType::MULTI_PROPERTIES);
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

        $this->waitReviewAndPost();

        $submitImportFile->click();

        $this->waitReviewAndPost(false);

        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());

        //Check notify tenant invite for new user
        $this->assertCount(0, $this->getEmails(), 'Wrong number of emails');
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
        $this->assertEquals('2025-01-31', $waitingContract->getFinishAt()->format('Y-m-d'));
        $this->assertTrue($unit->getProperty()->isSingle());

        $this->assertEquals(21, count($em->getRepository('RjDataBundle:ContractWaiting')->findAll()));
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
        $this->setPropertyFirst();
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

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();

        $this->waitReviewAndPost(false);

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
        $this->setPropertyFirst();
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
        $this->assertNotNull($importTypeSelected = $this->page->find('css', '#import_file_type_importType'));
        $importTypeSelected->selectOption(ImportType::MULTI_PROPERTIES);
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
        $this->assertNotNull($importTypeSelected = $this->page->find('css', '#import_file_type_importType'));
        $importTypeSelected->selectOption(ImportType::MULTI_PROPERTIES);
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
        $this->setPropertyFirst();
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
        $this->setPropertyFirst();
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
        $this->setPropertyFirst();
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
            '<span data-bind="text:$root.getResidentId($data)">t0016437</span>'
        );
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->session->wait(
            8000,
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
        $this->load(true);
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            array(
                'paymentAccepted' => PaymentAccepted::CASH_EQUIVALENT,
            )
        );
        $this->assertEquals(0, count($contract));
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findOneBy(
            array(
                'paymentAccepted' => PaymentAccepted::DO_NOT_ACCEPT,
            )
        );
        $this->assertEquals(0, count($contractWaiting));

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($submitImport = $this->page->find('css', '.submitImportFile'));
        $this->setPropertyFirst();
        $this->assertNotNull($yardiRadio = $this->page->findAll('css', '.radio'));
        $yardiRadio[1]->click();
        $this->assertNotNull($propertyId = $this->page->find('css', '#import_file_type_propertyId'));
        $propertyId->setValue('rnttrk01');
        $submitImport->click();

        $this->session->wait(
            250000,
            "$('table').is(':visible')"
        );
        $this->waitReviewAndPost(true);
        for ($i = 0; $i <= 4; $i++) {
            if ($errorFields = $this->page->findAll('css', '.errorField')) {
                $this->assertEquals(1, count($errorFields));
                $errorFields[0]->setValue('14test1111@mail.com');
            }
            $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
            $submitImportFile->click();
            $this->waitReviewAndPost(true);
        }

        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());
        $this->logout();

        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            array(
                'paymentAccepted' => PaymentAccepted::CASH_EQUIVALENT,
            )
        );
        $this->assertNotEmpty($contract);
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findOneBy(
            array(
                'paymentAccepted' => PaymentAccepted::DO_NOT_ACCEPT,
            )
        );
        $this->assertNotEmpty($contractWaiting);

        $contracts = $em->getRepository('RjDataBundle:Contract')->findBy(
            array(
                'externalLeaseId' => 't0012020',
            )
        );
        $this->assertCount(2, $contracts);
        $residentMapping = $em->getRepository('RjDataBundle:ResidentMapping')->findOneBy(
            array(
                'residentId' => 'r0004169',
            )
        );
        $this->assertNotEmpty($residentMapping);
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
        $this->setPropertyFirst();
        $this->assertNotNull($yardiRadio = $this->page->findAll('css', '.radio'));
        $yardiRadio[1]->click();
        $this->assertNotNull($propertyId = $this->page->find('css', '#import_file_type_propertyId'));
        $propertyId->setValue('rnttrk01');
        $this->assertNotNull($exceptionOnly = $this->page->find('css', '#import_file_type_onlyException'));
        $exceptionOnly->check();

        $submitImport->click();

        $this->session->wait(
            250000,
            "$('table').is(':visible')"
        );

        $this->waitReviewAndPost();

        $errorFields = $this->page->findAll('css', '.errorField');
        $this->assertEquals(1, count($errorFields));
        $errorFields[0]->setValue('tester@mail.com');
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost();

        $this->session->wait(
            10000,
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
        $this->assertNotNull($importTypeSelected = $this->page->find('css', '#import_file_type_importType'));
        $importTypeSelected->selectOption(ImportType::MULTI_PROPERTIES);
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
        $filePath = $this->getFilePathByName('import.csv');
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->setPropertyFirst();
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
        $filePath = $this->getFilePathByName('import.csv');
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->setPropertyFirst();
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
        $this->setPropertyFirst();
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
            $startAt = new DateTime();
            $startAt->modify("-5 month");
            $contract->setStartAt($startAt);
            $em->flush($contract);
        } else {
            $today = new DateTime();
            $this->assertEquals(count($operations = $contract->getOperations()), 1);
            $this->assertTrue(
                $contract->getPaidTo()->format('Ym') > $today->format('Ym'),
                "Contract paidTo date did not advance"
            );
            $this->assertTrue(
                $contract->getStartAt()->format('Ym') === $today->format('Ym'),
                "Contract startAt date did not advance"
            );
        }
    }

    /**
     * @test
     */
    public function importMultipleGroups()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->session->wait(5000, "typeof jQuery != 'undefined'");

        //First Step
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $this->assertNotNull($importTypeSelected = $this->page->find('css', '#import_file_type_importType'));
        $importTypeSelected->selectOption(ImportType::MULTI_GROUPS);
        $filePath = $this->getFilePathByName('import_multiple_group.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->assertNotNull($dateSelector = $this->page->find('css', '.import-date'));
        $dateSelector->selectOption('m/d/y');
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        foreach ($this->mapMultipleGroupFile as $i => $choiceOption) {
            $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
            $choice->selectOption($choiceOption);
        }

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $this->assertNull($errorFields = $this->page->find('css', 'input.errorField'));


        $trs = $this->getParsedTrsByStatus();

        $this->assertCount(2, $trs, "Count statuses is wrong");
        $this->assertCount(
            4,
            $trs['import.status.skip'],
            "One contract should be skipped, because we don't have such account number and 3 isn't integrated"
        );

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));

        $submitImportFile->click();

        $this->waitReviewAndPost(false);

        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());

        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $unitMapping = $em
            ->getRepository('RjDataBundle:UnitMapping')
            ->findOneBy(['externalUnitId' => 'SO1004-M']);

        $this->assertNotNull($unitMapping);
        /** @var \RentJeeves\DataBundle\Entity\Unit $unit */
        $this->assertNotNull($unit = $unitMapping->getUnit());
        // We sure that only one contract for this unit was created
        $this->assertNotNull($contract = $unit->getContracts()->first());
        $this->assertEquals('Test Rent Group', $contract->getGroup()->getName());
    }

    /**
     * @test
     */
    public function shouldOnlyNewAndException()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->setPropertyFirst();
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import_only_exception.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->assertNotNull($dateSelector = $this->page->find('css', '.import-date'));
        $dateSelector->selectOption('m/d/Y');
        $this->assertNotNull($exceptionOnly = $this->page->find('css', '#import_file_type_onlyException'));
        $exceptionOnly->check();
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
            15000,
            "$('.errorField').length > 0"
        );

        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertEquals(1, count($errorFields));
        $this->assertEquals($errorFields[0]->getValue(), '2testmail.com');

        $trs = $this->getParsedTrsByStatus();

        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.new']), "New contract on first page is wrong number");
        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertNotNull(
            $email = $trs['import.status.new'][0]->find('css', '.import_new_user_with_contract_tenant_email')
        );
        $email->setValue('2test@mail.com');
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost();

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
    public function resmanBaseImport()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');

        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var $landlord Landlord */
        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneByEmail('landlord1@example.com');
        /** @var AccountingSettings $accountingSettings */
        $accountingSettings = $landlord->getHolding()->getAccountingSettings();
        $accountingSettings->setApiIntegration(ApiIntegrationType::RESMAN);
        $em->flush($accountingSettings);
        $contract = $em->getRepository('RjDataBundle:Contract')->findAll();
        // We must make sure the data saved into DB, so we count before import and after
        $this->assertEquals(23, count($contract));
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertEquals(1, count($contractWaiting));

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($submitImport = $this->page->find('css', '.submitImportFile'));
        $this->setPropertySecond();
        $this->assertNotNull($source = $this->page->findAll('css', '.radio'));
        $source[1]->click();
        $this->assertNotNull($propertyId = $this->page->find('css', '#import_file_type_propertyId'));
        $propertyId->setValue('B342E58C-F5BA-4C63-B050-CF44439BB37D');
        $submitImport->click();

        $this->session->wait(
            80000,
            "$('table').is(':visible')"
        );
        $this->waitReviewAndPost();
        for ($i = 0; $i <= 2; $i++) {
            if ($i === 0) {
                $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
                $this->assertCount(1, $errorFields);
                $errorFields[0]->setValue('CorrrectName');
            }
            if ($i === 2) {
                $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
                $this->assertCount(1, $errorFields);
                $errorFields[0]->setValue('CorrrectName');
            }
            $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
            $submitImportFile->click();
            $this->waitReviewAndPost();
        }

        $this->logout();
        // We must make sure the data saved into DB, so we count before import and after
        $contracts = $em->getRepository('RjDataBundle:Contract')->findAll();
        $this->assertEquals(29, count($contracts));
        $contractsWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertEquals(22, count($contractsWaiting));
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            array(
                'externalLeaseId' => 'a0668dcf-045d-4183-926c-b7d50a571506',
            )
        );
        $this->assertNotEmpty($contract);
    }


    /**
     * @test
     */
    public function shouldGetException()
    {
        $this->load(true);
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $property = $em->getRepository('RjDataBundle:Property')->findOneBy(
            array(
                'street' => 'Broadway',
                'number' => '785',
                'zip'    => '10003'
            )
        );

        $units = $property->getUnits();
        foreach ($units as $unit) {
            $em->remove($unit);
        }

        $em->flush();
        $property->setIsSingle(true);
        $em->persist($property);
        $em->flush();
        $singlUnit = $property->getSingleUnit();
        $em->remove($singlUnit);
        $em->flush();

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

        $this->assertNotNull($propertySelector = $this->page->find('css', '#import_file_type_property'));
        $propertySelector->selectOption($property->getId());

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

        $this->assertNotNull($informationBox = $this->page->find('css', '.information-box>span'));
        $this->assertEquals('import.description_exception', $informationBox->getHtml());
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost(false);

        $this->assertNotNull($informationBox = $this->page->find('css', '.information-box>span'));
        $this->assertEquals('import.description_exception', $informationBox->getHtml());
        $this->assertNotNull($skipException = $this->page->find('css', '.skipException'));
        $skipException->click();
        $this->waitReviewAndPost(false);

        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals('import.review.finish', $finishedTitle->getHtml());
    }
}
