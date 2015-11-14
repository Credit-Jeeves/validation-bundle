<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ImportGroupSettings;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\DataBundle\Enum\ImportType;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as ImportMapping;

class ImportCsvCase extends ImportBaseAbstract
{
    /**
     * @param array $trs
     */
    protected function fillSecondPageWrongValue(array $trs)
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
     * @param array $map
     * @param int $limit
     */
    protected function fillCsvMapping(array $map, $limit)
    {
        for ($i = 1; $i <= $limit; $i++) {
            if (isset($map[$i])) {
                $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column' . $i));
                $choice->selectOption($map[$i]);
            }
        }
    }

    /**
     * @test
     */
    public function shouldImportFile()
    {
        $this->load(true);

        /** @var ImportGroupSettings $importGroupSettings */
        $importGroupSettings = $this->getEntityManager()->getRepository('RjDataBundle:ImportGroupSettings')->findOneBy(
            [
                'source' => 'integrated_api'
            ]
        );
        $this->assertNotEmpty($importGroupSettings, 'We do not have correct settings in fixtures');
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::SINGLE_PROPERTY);
        $importGroupSettings->setApiPropertyIds(null);
        $importGroupSettings->getGroup()->getHolding()->setApiIntegrationType(ApiIntegrationType::NONE);
        $this->getEntityManager()->flush();

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
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $mapFile = $this->mapFile;
        $mapFile[15] = ImportMapping::KEY_TENANT_STATUS;

        $this->fillCsvMapping($mapFile, 15);

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->session->wait(
            5000,
            "$('.errorField').length > 0"
        );

        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertCount(2, $errorFields);

        $this->assertEquals($errorFields[0]->getValue(), '2testmail.com');
        $this->assertEquals(
            trim($errorFields[1]->getHtml()),
            '<span data-bind="text:$root.getResidentId($data)">t0000020</span>'
        );

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
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertCount(1, $trs, 'Incorrect number of contracts');
        $this->assertCount(2, $trs['import.status.skip'], 'Count contract with status \'skip\' wrong');
        $submitImportFile->click();

        $this->waitRedirectToSummaryPage();
        $this->assertNotNull($publicId = $this->page->find('css', '#publicId'));
        //Check notify tenant invite for new user or update his contract rent
        $this->assertCount(9, $this->getEmails(), 'Wrong number of emails');
        $em = $this->getEntityManager();
        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(['email' => '2test@mail.com']);
        $this->assertNotNull($tenant);
        $this->assertEquals($tenant->getFirstName(), 'Trent Direnna');
        $this->assertEquals($tenant->getLastName(), 'Jacquelyn Dacey');
        $this->assertEquals($tenant->getResidentsMapping()->first()->getResidentId(), 't0019851');

        // Check that first and last names are parsed correctly when field contains coma.
        $tenant2 = $em->getRepository('RjDataBundle:Tenant')->findOneBy(['email' => '19test@mail.com']);
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
        $this->assertEquals('11/08/2025', $contract->getFinishAt()->format('m/d/Y'));

        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            ['email' => 'tenant11@example.com']
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
        $this->assertEquals('10/21/2025', $contractMatch->getFinishAt()->format('m/d/Y'));
        $this->assertEquals(ContractStatus::APPROVED, $contractMatch->getStatus());
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(['email' => 'hugo@rentrack.com']);

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
        $this->assertEquals('03/31/2025', $contractNew->getFinishAt()->format('m/d/Y'));
        $this->assertEquals(ContractStatus::APPROVED, $contractNew->getStatus());
        /** @var ImportSummary $importSummary */
        $importSummary = $em->getRepository('RjDataBundle:ImportSummary')->findOneBy(
            ['publicId' => $publicId->getText()]
        );
        $this->assertNotEmpty($importSummary);
        $this->assertEquals(20, $importSummary->getCountTotal());
        $this->assertEquals(1, $importSummary->getCountMatched());
        $this->assertEquals(9, $importSummary->getCountSkipped());
        $this->assertEquals(9, $importSummary->getCountNew());
        $this->assertEquals(1, $importSummary->countErrors());
        $this->assertEquals(0, $importSummary->countExceptions());
        $this->assertEquals(8, $importSummary->getCountInvited());
    }

    /**
     * @test
     * @depends shouldImportFile
     */
    public function shouldImportFileWithCheckboxOnlyNewAndException()
    {
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->setPropertyFirst();
        $this->assertNotNull($exceptionOnly = $this->page->find('css', '#import_file_type_onlyException'));
        $exceptionOnly->check();
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->setPropertyFirst();
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->session->wait(
            20000,
            "$('.errorField').length > 0"
        );

        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertCount(1, $errorFields);

        $this->assertEquals(
            trim($errorFields[0]->getHtml()),
            '<span data-bind="text:$root.getResidentId($data)">t0000020</span>'
        );

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();

        $this->waitRedirectToSummaryPage();
        $this->assertNotNull($publicId = $this->page->find('css', '#publicId'));
        /** @var ImportSummary $importSummary */
        $importSummary = $this->getEntityManager()->getRepository('RjDataBundle:ImportSummary')->findOneBy(
            ['publicId' => $publicId->getText()]
        );
        $this->assertNotEmpty($importSummary);
        $this->assertEquals(20, $importSummary->getCountTotal());
        $this->assertEquals(10, $importSummary->getCountMatched());
        $this->assertEquals(9, $importSummary->getCountSkipped());
        $this->assertEquals(0, $importSummary->getCountNew());
        $this->assertEquals(1, $importSummary->countErrors());
        $this->assertEquals(0, $importSummary->countExceptions());
        $this->assertEquals(0, $importSummary->getCountInvited());
    }

    /**
     * @test
     */
    public function waitingRoom()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');

        /** @var ImportGroupSettings $importGroupSettings */
        $importGroupSettings = $this->getEntityManager()->getRepository('RjDataBundle:ImportGroupSettings')->findOneBy(
            [
                'source' => 'integrated_api'
            ]
        );
        $this->assertNotEmpty($importGroupSettings, 'We do not have correct settings in fixtures');
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::SINGLE_PROPERTY);
        $importGroupSettings->setApiPropertyIds(null);
        $importGroupSettings->getGroup()->getHolding()->setApiIntegrationType(ApiIntegrationType::NONE);
        $this->getEntityManager()->flush();

        for ($i = 0; $i < 2; $i++) {
            $this->login('landlord1@example.com', 'pass');
            $this->page->clickLink('tab.accounting');
            //First Step
            $this->session->wait(5000, "typeof jQuery != 'undefined'");
            $this->setPropertyFirst();
            $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
            $filePath = $this->getFilePathByName('import_waiting_room.csv');
            $attFile->attachFile($filePath);

            $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
            $submitImportFile->click();

            $this->assertNull($error = $this->page->find('css', '.error_list>li'));
            //Second step
            $this->assertNotNull($table = $this->page->find('css', 'table'));

            for ($i = 1; $i <= 14; $i++) {
                $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column' . $i));
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
                "Waiting contract on first page is wrong number " . $i
            );
            $this->assertEquals(
                1,
                count($trs['import.status.skip']),
                "Skip contract on first page is wrong number " . $i
            );

            $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
            $submitImportFile->click();
            $this->waitRedirectToSummaryPage();
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
        $em = $this->getEntityManager();
        /**
         * @var $property Property
         */
        $property = $em->getRepository('RjDataBundle:Property')->findOneByPropertyAddressFields(
            [
                'jb' => '40.7308364',
                'kb' => '-73.991567',
            ]
        );

        $this->assertNotNull($property);

        $this->session->visit($this->getUrl() . 'user/new/' . $property->getId());
        $this->assertNotNull($thisIsMyRental = $this->page->find('css', '.thisIsMyRental'));
        $thisIsMyRental->click();
        $this->assertNotNull($form = $this->page->find('css', '#formNewUser'));
        $this->fillForm(
            $form,
            array(
                'rentjeeves_publicbundle_tenanttype_first_name' => $contractWaiting->getFirstName() . 'Wr',
                'rentjeeves_publicbundle_tenanttype_last_name' => $contractWaiting->getLastName(),
                'rentjeeves_publicbundle_tenanttype_email' => 'hi@mail.com',
                'rentjeeves_publicbundle_tenanttype_password_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_tos' => true,
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
                'rentjeeves_publicbundle_tenanttype_first_name' => $contractWaiting->getFirstName(),
                'rentjeeves_publicbundle_tenanttype_last_name' => $contractWaiting->getLastName(),
                'rentjeeves_publicbundle_tenanttype_email' => 'hi@mail.com',
                'rentjeeves_publicbundle_tenanttype_password_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_tos' => true,
            )
        );

        $this->assertNotNull($submit = $this->page->find('css', '#register'));
        $submit->click();
        $fields = $this->page->findAll('css', '#inviteText>h4');
        $this->assertCount(2, $fields, 'wrong number of text h4');

        //Check contract

        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(['email' => 'hi@mail.com']);
        $this->assertNotNull($tenant);
        $contracts = $tenant->getContracts();
        $this->assertEquals(1, count($contracts));
        /**
         * @var Contract $contract
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
        $em = $this->getEntityManager();
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
        //Second step
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($this->mapFile, 14);

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
        $this->waitRedirectToSummaryPage();
        $this->logout();
    }


    /**
     * @test
     */
    public function checkFormatDate()
    {
        $this->load(true);

        /** @var ImportGroupSettings $importGroupSettings */
        $importGroupSettings = $this->getEntityManager()->getRepository('RjDataBundle:ImportGroupSettings')->findOneBy(
            [
                'source' => 'integrated_api'
            ]
        );
        $this->assertNotEmpty($importGroupSettings, 'We do not have correct settings in fixtures');
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::SINGLE_PROPERTY);
        $importGroupSettings->setApiPropertyIds(null);
        $importGroupSettings->setCsvDateFormat('n-j-Y');
        $importGroupSettings->getGroup()->getHolding()->setApiIntegrationType(ApiIntegrationType::NONE);
        $this->getEntityManager()->flush();

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
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $submitImportFile->click();
        //Second step
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($this->mapFile, 14);

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

    /**
     * @test
     */
    public function importMultipleProperties()
    {
        $this->load(true);
        $em = $this->getEntityManager();

        /** @var ImportGroupSettings $importGroupSettings */
        $importGroupSettings = $this->getEntityManager()->getRepository('RjDataBundle:ImportGroupSettings')->findOneBy(
            [
                'source' => 'integrated_api'
            ]
        );
        $this->assertNotEmpty($importGroupSettings, 'We do not have correct settings in fixtures');
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::MULTI_PROPERTIES);
        $importGroupSettings->setApiPropertyIds(null);
        $importGroupSettings->setCsvDateFormat('m/d/y');
        $importGroupSettings->getGroup()->getHolding()->setApiIntegrationType(ApiIntegrationType::NONE);
        $this->getEntityManager()->flush();

        $this->assertEquals(1, count($em->getRepository('RjDataBundle:ContractWaiting')->findAll()));
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->session->wait(5000, "typeof jQuery != 'undefined'");

        //First Step
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import_multiple.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        //second step
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));
        $this->fillCsvMapping($this->mapMultiplePropertyFile, 17);

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->session->wait(
            5000,
            "$('.errorField').length > 0"
        );
        $this->waitReviewAndPost();

        // first page: all contract waiting and 5 name errors.
        $this->assertNotNull($errorFields = $this->page->findAll('css', 'input.errorField'));
        $this->assertEquals(5, count($errorFields));

        $this->assertEquals('', $errorFields[0]->getValue());
        $this->assertEquals('', $errorFields[1]->getValue());
        $this->assertEquals('& Adelai', $errorFields[2]->getValue());
        $this->assertEquals('Carol Acha.Mo', $errorFields[3]->getValue());
        $this->assertEquals('Matthew &', $errorFields[4]->getValue());

        $trs = $this->getParsedTrsByStatus();

        $this->assertEquals(1, count($trs), "Should only have 1 type of status");
        $this->assertEquals(9, count($trs['import.status.waiting']), "All contracts should be waiting");

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));

        // first page: fix errors and continue
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

        // second page: all contract waiting and no errors.
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(9, count($trs['import.status.waiting']), "All contracts should be waiting");

        $this->assertNotNull($errorFields = $this->page->findAll('css', 'input.errorField'));
        $this->assertEquals(0, count($errorFields));
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));

        $submitImportFile->click();

        $this->waitReviewAndPost();

        // third page: verify no errors and finish
        $submitImportFile->click();

        $this->waitReviewAndPost();
        $this->waitRedirectToSummaryPage();

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
                'residentId' => 'ABBOTT,MIT',
                'firstName' => 'Logan',
                'lastName' => 'Cooper',
                'property' => $unit->getProperty()->getId(),
                'unit' => $unit->getId(),
            )
        );
        $this->assertNotNull($waitingContract);
        $this->assertEquals(975, $waitingContract->getRent());
        $this->assertEquals(193, $waitingContract->getIntegratedBalance());
        $this->assertEquals('2014-01-01', $waitingContract->getStartAt()->format('Y-m-d'));
        $this->assertEquals('2025-01-31', $waitingContract->getFinishAt()->format('Y-m-d'));
        $this->assertTrue($unit->getProperty()->getPropertyAddress()->isSingle());

        $this->assertEquals(21, count($em->getRepository('RjDataBundle:ContractWaiting')->findAll()));
    }

    /**
     * @test
     * @depends importMultipleProperties
     */
    public function signUpFromImportedWaitingContract()
    {
        # Check this issue
        #$this->markTestSkipped('Temporarily skip this test due to: PHP Fatal error:  Allowed memory size exhausted');
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
                'rentjeeves_publicbundle_tenanttype_first_name' => "Daniel",
                'rentjeeves_publicbundle_tenanttype_last_name' => "Price",
                'rentjeeves_publicbundle_tenanttype_email' => 'dan.price@mail.com',
                'rentjeeves_publicbundle_tenanttype_password_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_tos' => true,
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

}
