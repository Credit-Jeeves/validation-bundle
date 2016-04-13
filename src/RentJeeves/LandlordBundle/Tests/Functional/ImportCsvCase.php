<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\ImportGroupSettings;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\DataBundle\Enum\ImportType;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
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
     * @test
     */
    public function shouldImportFile()
    {
        $this->load(true);

        $importGroupSettings = $this->getImportGroupSettings();
        $this->assertNotEmpty($importGroupSettings, 'We do not have correct settings in fixtures');
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::SINGLE_PROPERTY);
        $importGroupSettings->setApiPropertyIds(null);
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::NONE);
        $this->getEntityManager()->flush();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");

        $submitImportFile = $this->getDomElement('.submitImportFile');
        $submitImportFile->click();
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list li'));
        $this->assertCount(2, $errors, 'Wrong number of errors');
        $this->assertEquals('import.errors.single_property_select', $errors[0]->getHtml());
        $this->assertEquals('error.file.empty', $errors[1]->getHtml());

        $this->setPropertyFirst();
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('import_failed.csv');
        $attFile->attachFile($filePath);
        $submitImportFile->click();
        $this->assertNotNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertEquals('csv.file.too.small1', $error->getHtml());
        $this->assertNotNull($prev = $this->page->find('css', '.button'));
        $prev->click();
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('import.csv');
        $attFile->attachFile($filePath);
        $this->setPropertyFirst();
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $mapFile = $this->mapFile;
        $mapFile[15] = ImportMapping::KEY_TENANT_STATUS;

        $this->fillCsvMapping($mapFile, 15);

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
        $filePath = $this->getFixtureFilePathByName('import.csv');
        $attFile->attachFile($filePath);
        $submitImportFile = $this->getDomElement('.submitImportFile');
        $this->setPropertyFirst();
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));
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
        $importGroupSettings = $this->getImportGroupSettings();
        $this->assertNotEmpty($importGroupSettings, 'We do not have correct settings in fixtures');
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::SINGLE_PROPERTY);
        $importGroupSettings->setApiPropertyIds(null);
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::NONE);
        $this->getEntityManager()->flush();

        for ($i = 0; $i < 2; $i++) {
            $this->login('landlord1@example.com', 'pass');
            $this->page->clickLink('tab.accounting');
            //First Step
            $this->session->wait(5000, "typeof jQuery != 'undefined'");
            $this->setPropertyFirst();
            $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
            $filePath = $this->getFixtureFilePathByName('import_waiting_room.csv');
            $attFile->attachFile($filePath);

            $submitImportFile = $this->getDomElement('.submitImportFile');
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

            $submitImportFile->click();
            $this->waitRedirectToSummaryPage();
            $this->logout();
        }

        $this->getContractInWaitingStatus();
    }

    /**
     * @depends waitingRoom
     * @test
     */
    public function createContractFromWaiting()
    {
        $contractInWaitingStatus = $this->getContractInWaitingStatus();
        $tenantWithoutEmail = $contractInWaitingStatus->getTenant();
        $em = $this->getEntityManager();
        /**
         * @var $property Property
         */
        $property = $em->getRepository('RjDataBundle:Property')->findOneByPropertyAddressFields(
            [
                'lat' => '40.73108',
                'long' => '-73.99186',
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
                'rentjeeves_publicbundle_tenanttype_first_name' => $tenantWithoutEmail->getFirstName() . 'Wr',
                'rentjeeves_publicbundle_tenanttype_last_name' => $tenantWithoutEmail->getLastName(),
                'rentjeeves_publicbundle_tenanttype_email' => 'hi@mail.com',
                'rentjeeves_publicbundle_tenanttype_password_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_tos' => true,
            )
        );

        $this->assertNotNull($selectUnit = $this->page->find('css', '.select-unit'));
        $selectUnit->selectOption($contractInWaitingStatus->getUnit()->getName());

        $this->assertNotNull($submit = $this->page->find('css', '#register'));
        $submit->click();
        $this->assertNotNull($unitReserved = $this->page->find('css', '.error_list>li'));
        $this->assertEquals('error.unit.reserved', $unitReserved->getHtml());

        $this->assertNotNull($form = $this->page->find('css', '#formNewUser'));
        $this->fillForm(
            $form,
            array(
                'rentjeeves_publicbundle_tenanttype_first_name' => $tenantWithoutEmail->getFirstName(),
                'rentjeeves_publicbundle_tenanttype_last_name' => $tenantWithoutEmail->getLastName(),
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
        $this->assertEquals($contract->getStartAt(), $contractInWaitingStatus->getStartAt());
        $this->assertEquals($contract->getFinishAt(), $contractInWaitingStatus->getFinishAt());
        $this->assertEquals($contract->getRent(), $contractInWaitingStatus->getRent());
        $this->assertEquals($contract->getIntegratedBalance(), $contractInWaitingStatus->getIntegratedBalance());
        $this->assertEquals($contract->getStartAt(), $contractInWaitingStatus->getStartAt());
        $this->assertEquals($contract->getUnit()->getId(), $contractInWaitingStatus->getUnit()->getId());

        $mapping = $tenant->getResidentsMapping();
        $this->assertEquals(1, count($mapping));
        /**
         * @var $mapping ResidentMapping
         */
        $mapping = $mapping[0];
        $this->assertEquals(
            $mapping->getResidentId(),
            $contractInWaitingStatus->getTenant()->getResidentsMapping()->first()->getResidentId(),
            'ResidentID doesn\'t exist'
        );
        $contractInWaitingStatus = $em->getRepository('RjDataBundle:Contract')->find($contractInWaitingStatus->getId());
        $this->assertNotNull($contractInWaitingStatus);
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
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(['email' => 'hi@mail.com']);

        $tenant->setEmailField('h1_changed@mail.com');
        $em->persist($tenant);
        $em->flush();

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $filePath = $this->getFixtureFilePathByName('import_waiting_room.csv');
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $attFile->attachFile($filePath);
        $this->setPropertyFirst();
        $submitImportFile = $this->getDomElement('.submitImportFile');
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        //Second step
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($this->mapFile, 14);

        //Map Payment
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
        $importGroupSettings = $this->getImportGroupSettings();
        $this->assertNotEmpty($importGroupSettings, 'We do not have correct settings in fixtures');
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::SINGLE_PROPERTY);
        $importGroupSettings->setApiPropertyIds(null);
        $importGroupSettings->setCsvDateFormat('n-j-Y');
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::NONE);
        $this->getEntityManager()->flush();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $filePath = $this->getFixtureFilePathByName('import_date_format.csv');
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $this->setPropertyFirst();
        $attFile->attachFile($filePath);
        $submitImportFile = $this->getDomElement('.submitImportFile');
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $submitImportFile->click();
        //Second step
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($this->mapFile, 14);

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
        $importGroupSettings = $this->getImportGroupSettings();
        $this->assertNotEmpty($importGroupSettings, 'We do not have correct settings in fixtures');
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::MULTI_PROPERTIES);
        $importGroupSettings->setApiPropertyIds(null);
        $importGroupSettings->setCsvDateFormat('m/d/y');
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::NONE);
        $this->getEntityManager()->flush();

        $this->assertEquals(
            0,
            count($em->getRepository('RjDataBundle:Contract')->findBy(['status' => ContractStatus::WAITING]))
        );
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->session->wait(5000, "typeof jQuery != 'undefined'");

        //First Step
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('import_multiple.csv');
        $attFile->attachFile($filePath);
        $submitImportFile = $this->getDomElement('.submitImportFile');
        $submitImportFile->click();
        //second step
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));
        $this->fillCsvMapping($this->mapMultiplePropertyFile, 17);

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

        $submitImportFile->click();

        $this->waitReviewAndPost();

        // third page: verify no errors and finish
        $submitImportFile->click();

        $this->waitRedirectToSummaryPage();

        //Check notify tenant invite for new user
        $this->assertCount(0, $this->getEmails(), 'Wrong number of emails');
        $unitMapping = $em->getRepository('RjDataBundle:UnitMapping')->findOneBy(
            ['externalUnitId' => 'SP1152-C']
        );
        $this->assertNotNull($unitMapping);
        $this->assertNotNull($unit = $unitMapping->getUnit());

        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            [
                'first_name' => 'Logan',
                'last_name' => 'Cooper',
            ]
        );
        $this->assertNotNull($tenant, 'Empty tenant Logan Cooper');
        $residentMapping = $tenant->getResidentsMapping()->first();
        $this->assertEquals('ABBOTT,MIT', $residentMapping->getResidentId(), 'Resident ID should be equlas');
        $contract = $tenant->getContracts()->first();
        $this->assertNotNull($contract);
        $this->assertEquals(975, $contract->getRent());
        $this->assertEquals(193, $contract->getIntegratedBalance());
        $this->assertEquals('2014-01-01', $contract->getStartAt()->format('Y-m-d'));
        $this->assertEquals('2025-01-31', $contract->getFinishAt()->format('Y-m-d'));
        $this->assertTrue($unit->getProperty()->getPropertyAddress()->isSingle());

        $this->assertEquals(
            20,
            count($em->getRepository('RjDataBundle:Contract')->findBy(['status' => ContractStatus::WAITING]))
        );
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
            'first_name' => 'Daniel',
            'last_name' => 'Price',
        );
        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            $waitingContractParams
        );
        $this->assertNotNull($tenant);
        $this->assertEmpty($tenant->getEmail(), 'Email should empty');
        $this->session->visit($this->getUrl() . 'iframe');
        $this->session->wait($this->timeout, "typeof $ !== undefined");

        $address = '813 West Nopal Place, Chandler, AZ 85225';
        $this->assertNotNull($form = $this->page->find('css', '#formSearch'));
        $this->assertNotNull($propertySearch = $this->page->find('css', '#property-add'));
        $this->fillForm(
            $form,
            ['property-search' => $address]
        );

        $propertySearch->click();
        $this->session->wait($this->timeout, "window.location.pathname.match('\/user\/new\/[0-9]') != null");
        $this->session->wait($this->timeout, "$('#register').length > 0");

        $this->assertNotNull($form = $this->page->find('css', '#formNewUser'));
        $this->fillForm(
            $form,
            [
                'rentjeeves_publicbundle_tenanttype_first_name' => "Daniel",
                'rentjeeves_publicbundle_tenanttype_last_name' => "Price",
                'rentjeeves_publicbundle_tenanttype_email' => 'dan.price@mail.com',
                'rentjeeves_publicbundle_tenanttype_password_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_tos' => true,
            ]
        );
        $this->assertNotNull($thisIsMyRental = $this->page->find('css', '.thisIsMyRental'));
        $thisIsMyRental->click();
        $this->assertNotNull($submit = $this->page->find('css', '#register'));
        $submit->click();

        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            ['email' => 'dan.price@mail.com']
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

        /** @var ImportGroupSettings $importGroupSettings */
        $importGroupSettings = $this->getImportGroupSettings();
        $this->assertNotEmpty($importGroupSettings, 'We do not have correct settings in fixtures');
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::SINGLE_PROPERTY);
        $importGroupSettings->setApiPropertyIds(null);
        $importGroupSettings->setCsvDateFormat('m/d/Y');
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::NONE);
        $this->getEntityManager()->flush();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('import_one_user.csv');
        $attFile->attachFile($filePath);
        $submitImportFile = $this->getDomElement('.submitImportFile');
        $this->setPropertyFirst();
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($this->mapFile, 14);

        $submitImportFile->click();

        $this->waitReviewAndPost(false);
        $this->assertNotNull($invite = $this->page->find('css', '.0_sendInvite'));
        $invite->check();
        $submitImportFile->click();

        $this->waitRedirectToSummaryPage();
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

        $em = $this->getEntityManager();
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            array(
                'rent' => 777666.00,
                'integratedBalance' => 1277.00
            )
        );

        $this->assertNotNull($contract);
        $this->assertEquals('tenant11@example.com', $contract->getTenant()->getEmail());
    }

    /**
     * @test
     * @depends alreadyHaveAccount
     */
    public function checkMatchedUser()
    {
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('import_one_user.csv');
        $attFile->attachFile($filePath);
        $submitImportFile = $this->getDomElement('.submitImportFile');
        $this->setPropertyFirst();
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($this->mapFile, 14);

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

        /** @var ImportGroupSettings $importGroupSettings */
        $importGroupSettings = $this->getImportGroupSettings();
        $this->assertNotEmpty($importGroupSettings, 'We do not have correct settings in fixtures');
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::MULTI_PROPERTIES);
        $importGroupSettings->setApiPropertyIds(null);
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::NONE);
        $this->getEntityManager()->flush();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('duplicate_waiting_room.csv');
        $attFile->attachFile($filePath);
        $submitImportFile = $this->getDomElement('.submitImportFile');
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $mapFile = [
            '1' => ImportMapping::KEY_RESIDENT_ID,
            '2' => ImportMapping::KEY_TENANT_NAME,
            '3' => ImportMapping::KEY_RENT,
            '4' => ImportMapping::KEY_BALANCE,
            '5' => ImportMapping::KEY_UNIT_ID,
            '6' => ImportMapping::KEY_STREET,
            '7' => ImportMapping::KEY_CITY,
            '8' => ImportMapping::KEY_STATE,
            '9' => ImportMapping::KEY_ZIP,
            '10' => ImportMapping::KEY_MOVE_IN,
            '11' => ImportMapping::KEY_LEASE_END,
            '12' => ImportMapping::KEY_MOVE_OUT,
            '14' => ImportMapping::KEY_EMAIL,
        ];
        $this->fillCsvMapping($mapFile, 15);

        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.waiting']), "Waiting contract is wrong number");
        $this->assertNotNull($firstName1 = $this->page->find('css', 'input.0_first_name'));
        $firstName1->setValue('Logan');
        $this->assertNotNull($lastName1 = $this->page->find('css', 'input.0_last_name'));
        $lastName1->setValue('Cooper');
        $submitImportFile->click();
        $this->waitRedirectToSummaryPage();
        //after that check mathced status
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('duplicate_waiting_room.csv');
        $attFile->attachFile($filePath);
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($mapFile, 15);

        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.match']), "Match contract is wrong number");
        $submitImportFile->click();
        $this->waitRedirectToSummaryPage();
    }

    /**
     * @return array
     */
    public function providerForMatchWaitingContractWithMoveContract()
    {
        return [
            [100.01],
            [-10.02]
        ];
    }

    /**
     * @test
     * @dataProvider providerForMatchWaitingContractWithMoveContract
     */
    public function matchWaitingContractWithMoveContract($balanceIn)
    {
        $this->load(true);
        $importGroupSettings = $this->getImportGroupSettings();
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::SINGLE_PROPERTY);
        $importGroupSettings->setApiPropertyIds(null);
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::NONE);
        $this->getEntityManager()->flush();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('one_user_for_waiting_room.csv');
        $attFile->attachFile($filePath);
        $this->setPropertyFirst();
        $submitImportFile = $this->getDomElement('.submitImportFile');
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $mapFile = [
            '1' => ImportMapping::KEY_UNIT,
            '2' => ImportMapping::KEY_RESIDENT_ID,
            '3' => ImportMapping::KEY_TENANT_NAME,
            '4' => ImportMapping::KEY_RENT,
            '5' => ImportMapping::KEY_MOVE_IN,
            '6' => ImportMapping::KEY_LEASE_END,
            '7' => ImportMapping::KEY_MOVE_OUT,
            '8' => ImportMapping::KEY_BALANCE,
            '9' => ImportMapping::KEY_EMAIL,
        ];
        $this->fillCsvMapping($mapFile, 15);

        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.new']), "New contract is wrong number");
        $this->assertNotNull($email = $this->page->find('css', 'input.0_email'));
        $email->setValue('');
        $this->assertNotNull($balance = $this->page->find('css', 'input.0_balance'));
        $balance->setValue($balanceIn);
        $submitImportFile->click();
        $this->waitRedirectToSummaryPage();
        //after that check mathced status
        $this->page->clickLink('tab.accounting');

        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('one_user_for_waiting_room.csv');
        $attFile->attachFile($filePath);
        $this->setPropertyFirst();
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
        $this->fillCsvMapping($mapFile, 15);

        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.match']), "Match contract is wrong number");
        $this->assertNotNull($balance = $this->page->find('css', 'input.0_balance'));
        $balance->setValue($balanceIn);
        $submitImportFile->click();
        $this->waitRedirectToSummaryPage();

        $em = $this->getEntityManager();
        $contracts = $em->getRepository('RjDataBundle:Contract')->findBy(
            ['integratedBalance' => $balanceIn]
        );
        $this->assertEquals(1, count($contracts));
        /** @var Contract $contract */
        $contract = end($contracts);
        $reflectionClass = new \ReflectionClass($contract);
        $reflectionProperty = $reflectionClass->getProperty('paidTo');
        $reflectionProperty->setAccessible(true);
        $paidTo = $reflectionProperty->getValue($contract);
        $this->assertNull($paidTo, 'We should not set paid to, it should be null');
    }

    /**
     * @test
     */
    public function importMultipleGroups()
    {
        $this->load(true);

        $importGroupSettings = $this->getImportGroupSettings();
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::MULTI_GROUPS);
        $importGroupSettings->setApiPropertyIds(null);
        $importGroupSettings->setCsvDateFormat('d/m/y');
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::NONE);
        $this->getEntityManager()->flush();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->session->wait(5000, "typeof jQuery != 'undefined'");

        //First Step
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('import_multiple_group.csv');
        $attFile->attachFile($filePath);
        $submitImportFile = $this->getDomElement('.submitImportFile');
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        foreach ($this->mapMultipleGroupFile as $i => $choiceOption) {
            $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column' . $i));
            $choice->selectOption($choiceOption);
        }

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

        $submitImportFile->click();

        $this->waitRedirectToSummaryPage();
        $em = $this->getEntityManager();

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

        $importGroupSettings = $this->getImportGroupSettings();
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::SINGLE_PROPERTY);
        $importGroupSettings->setApiPropertyIds(null);
        $importGroupSettings->setCsvDateFormat('m/d/Y');
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::NONE);
        $this->getEntityManager()->flush();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->setPropertyFirst();
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('import_only_exception.csv');
        $attFile->attachFile($filePath);
        $submitImportFile = $this->getDomElement('.submitImportFile');
        $this->assertNotNull($exceptionOnly = $this->page->find('css', '#import_file_type_onlyException'));
        $exceptionOnly->check();
        $submitImportFile->click();

        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($this->mapFile, 14);

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
        $submitImportFile->click();

        $this->waitRedirectToSummaryPage();
    }

    /**
     * @test
     */
    public function shouldThrowExceptionForImportSinglePropertyWithoutUnit()
    {
        $this->load(true);
        $em = $this->getEntityManager();
        /** @var Property $property */
        $property = $em->getRepository('RjDataBundle:Property')->findOneByPropertyAddressFields(
            [
                'street' => 'Broadway',
                'number' => '785',
                'zip' => '10003'
            ]
        );
        /*
         * Create a property with no units
         */
        $units = $property->getUnits();
        /** @var Unit $unit */
        foreach ($units as $unit) {
            $em->remove($unit);
        }
        $em->flush();
        $propertyAddress = $property->getPropertyAddress();
        $propertyAddress->setIsSingle(true);

        $em->persist($property);
        $em->flush();

        $importGroupSettings = $this->getImportGroupSettings();
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::SINGLE_PROPERTY);
        $importGroupSettings->setApiPropertyIds(null);
        $importGroupSettings->setCsvDateFormat('m/d/Y');
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::NONE);
        $this->getEntityManager()->flush();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('import_one_user.csv');
        $attFile->attachFile($filePath);
        $submitImportFile = $this->getDomElement('.submitImportFile');

        $this->assertNotNull($propertySelector = $this->page->find('css', '#import_file_type_property'));
        $propertySelector->selectOption($property->getId());

        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($this->mapFile, 14);

        $submitImportFile->click();

        $this->assertNotNull($informationBox = $this->page->find('css', '.information-box>span'));
        $this->assertEquals('import.description_exception', $informationBox->getHtml());
        $submitImportFile->click();
        $this->waitReview();

        $this->assertNotNull($informationBox = $this->page->find('css', '.information-box>span'));
        $this->assertEquals('import.description_exception', $informationBox->getHtml());
        $this->assertNotNull($skipException = $this->page->find('css', '.skipException'));
        $skipException->click();

        $this->waitRedirectToSummaryPage();
    }

    /**
     * @test
     */
    public function shouldGetMappingForImport()
    {
        $em = $this->getEntityManager();
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
    public function shouldImportPromasExtraField()
    {
        $this->load(true);

        $importGroupSettings = $this->getImportGroupSettings();
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::SINGLE_PROPERTY);
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::NONE);
        $this->getEntityManager()->flush();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('promas_extra_field.csv');
        $attFile->attachFile($filePath);
        $this->setPropertyFirst();
        $submitImportFile = $this->getDomElement('.submitImportFile');
        $submitImportFile->click();
        //Second Step
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $mapFile = [
            '1' => ImportMapping::KEY_UNIT,
            '2' => ImportMapping::KEY_RESIDENT_ID,
            '3' => ImportMapping::KEY_TENANT_NAME,
            '4' => ImportMapping::KEY_RENT,
            '5' => ImportMapping::KEY_BALANCE,
            '6' => ImportMapping::KEY_MOVE_IN,
            '7' => ImportMapping::KEY_LEASE_END,
            '8' => ImportMapping::KEY_MOVE_OUT,
            '9' => ImportMapping::KEY_EMAIL,
            '10' => ImportMapping::KEY_USER_PHONE,
            '11' => ImportMapping::KEY_CREDITS,
            '13' => ImportMapping::KEY_PAYMENT_ACCEPTED,
        ];

        $this->fillCsvMapping($mapFile, 13);

        $submitImportFile->click();
        $this->waitReviewAndPost();

        $trs = $this->getParsedTrsByStatus();
        $this->assertCount(1, $trs, "Count statuses is wrong");
        $this->assertCount(3, $trs['import.status.new'], "Count of new contracts is wrong");

        $submitImportFile->click();
        $this->waitReviewAndPost();

        $em = $this->getEntityManager();
        /** @var Tenant $userWithPhone */
        $userWithPhone = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('user_phone@mail.com');
        /** @var Tenant $userWithOpenCredits */
        $userWithOpenCredits = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('open_credits@mail.com');
        /** @var Tenant $userWithPaymentAccepted */
        $userWithPaymentAccepted = $em->getRepository('RjDataBundle:Tenant')
            ->findOneByEmail('payment_accepted@mail.com');

        $this->assertNotNull($userWithPhone);
        $this->assertNotNull($userWithOpenCredits);
        $this->assertNotNull($userWithPaymentAccepted);

        $this->assertEquals('0978822205', $userWithPhone->getPhone());

        $contractPhoneUser = $userWithPhone->getContracts()->first();
        $this->assertNotEmpty($contractPhoneUser);
        $this->assertEquals(
            PaymentAccepted::DO_NOT_ACCEPT,
            $contractPhoneUser->getPaymentAccepted()
        );
        $contractOpenCredits = $userWithOpenCredits->getContracts()->first();
        $this->assertNotEmpty($contractOpenCredits);
        $this->assertEquals(
            PaymentAccepted::DO_NOT_ACCEPT,
            $contractOpenCredits->getPaymentAccepted()
        );
        $contractPaymentAccepted = $userWithPaymentAccepted->getContracts()->first();
        $this->assertNotEmpty($contractPaymentAccepted);
        $this->assertEquals(
            PaymentAccepted::ANY,
            $contractPaymentAccepted->getPaymentAccepted()
        );

        $this->assertEquals(
            200,
            $contractOpenCredits->getIntegratedBalance()
        );
    }

    /**
     * @test
     */
    public function shouldCreateContractFromWaitingOnOnlyNewAndException()
    {
        $this->load(true);

        $importGroupSettings = $this->getImportGroupSettings();
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::SINGLE_PROPERTY);
        $importGroupSettings->setCsvDateFormat('m/d/Y');
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::NONE);
        $this->getEntityManager()->flush();

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $beforeContracts = $em->getRepository('RjDataBundle:Contract')->findBy(['status' => ContractStatus::WAITING]);

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->setPropertyFirst();
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('import_waiting_only_exception.csv');
        $attFile->attachFile($filePath);
        $submitImportFile = $this->getDomElement('.submitImportFile');
        $this->assertNotNull($exceptionOnly = $this->page->find('css', '#import_file_type_onlyException'));
        $exceptionOnly->check();
        $submitImportFile->click();

        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($this->mapFile, 14);

        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(2, count($trs), "Count statuses is wrong");
        $submitImportFile->click();

        $this->waitRedirectToSummaryPage();

        $afterContracts = $em->getRepository('RjDataBundle:Contract')->findBy(['status' => ContractStatus::WAITING]);
        $this->assertCount(count($beforeContracts) + 1, $afterContracts);
    }

    /**
     * @test
     */
    public function shouldImportCurrentPromasTenantsAndSetMonthToMonthToTrueIfMonthToMonthIsNo()
    {
        $this->load(true);
        $importGroupSettings = $this->getImportGroupSettings();
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::SINGLE_PROPERTY);
        $importGroupSettings->setCsvDateFormat('m/d/Y');
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::NONE);
        $this->getEntityManager()->flush();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');

        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('import_current_tenant.csv');
        $attFile->attachFile($filePath);
        $submitImportFile = $this->getDomElement('.submitImportFile');
        $this->setPropertyFirst();
        $submitImportFile->click();
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $mapFile = $this->mapFile;
        $mapFile[15] = ImportMapping::KEY_TENANT_STATUS;
        $mapFile[16] = ImportMapping::KEY_MONTH_TO_MONTH;

        $this->fillCsvMapping($mapFile, 16);

        $submitImportFile->click();
        $this->session->wait(1000, "$('table').is(':visible')");

        $importContractStatuses = $this->getParsedTrsByStatus();

        $this->assertCount(1, $importContractStatuses, '1 contract status should be found: "new"');
        $this->assertCount(2, $importContractStatuses['import.status.new'], '2 new contracts should be imported');

        $submitImportFile->click();

        $this->waitRedirectToSummaryPage();
        $this->assertNotNull($publicId = $this->page->find('css', '#publicId'));
    }

    /**
     * @test
     */
    public function duplicateResidentIdShouldBeSkippedWithError()
    {
        $this->load(true);
        $importGroupSettings = $this->getImportGroupSettings();
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::SINGLE_PROPERTY);
        $importGroupSettings->setCsvDateFormat('m/d/Y');
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::NONE);
        $this->getEntityManager()->flush();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('import_two_user.csv');
        $attFile->attachFile($filePath);
        $submitImportFile = $this->getDomElement('.submitImportFile');
        $this->setPropertyFirst();
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($this->mapFile, 14);

        $submitImportFile->click();
        $this->session->wait(
            5000,
            "$('.errorField').length > 0"
        );
        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertEquals(1, count($errorFields));
        $this->assertEquals($errorFields[0]->getHtml(), '15test@mail.com');
        $submitImportFile->click();
        $this->waitRedirectToSummaryPage();
        $em = $this->getEntityManager();
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
    public function skippedMessageAndInfoDateInvalid()
    {
        $this->load(true);

        $importGroupSettings = $this->getImportGroupSettings();
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::MULTI_PROPERTIES);
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::NONE);
        $this->getEntityManager()->flush();

        // get count of property
        /** @var PropertyRepository $repo */
        $repo = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:Property');
        $count = $repo->createQueryBuilder('p')->select('count(p.id)')->getQuery()->getSingleScalarResult();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('skipped_message_and_date_notice.csv');
        $attFile->attachFile($filePath);
        $submitImportFile = $this->getDomElement('.submitImportFile');
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
            '10' => ImportMapping::KEY_MOVE_IN,
            '11' => ImportMapping::KEY_LEASE_END,
            '12' => ImportMapping::KEY_MOVE_OUT,
            '14' => ImportMapping::KEY_EMAIL,
        );
        $this->fillCsvMapping($mapFile, 15);

        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(2, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.waiting']), "Waiting contract is wrong number");
        $this->assertEquals(1, count($trs['import.status.skip']), "Skip contract is wrong number");

        $this->assertNotNull($info = $this->page->find('css', '.information-box'));
        $this->assertEquals('import.error.mapping_date', trim($info->getHtml()));
        $this->assertNotNull($td = $this->page->find('css', '.line_number_1 td'));
        $this->assertEquals('import.info.skipped2', trim($td->getAttribute('title')));
        // check that added 2 new properties from import file
        $countNew = $repo->createQueryBuilder('p')->select('count(p.id)')->getQuery()->getSingleScalarResult();
        $this->assertEquals($countNew, $count + 2);
    }

    /**
     * @test
     */
    public function shouldImportFileWithExternalLeaseId()
    {
        $this->load(true);

        $importGroupSettings = $this->getImportGroupSettings();
        $this->assertNotEmpty($importGroupSettings, 'We do not have correct settings in fixtures');
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::SINGLE_PROPERTY);
        $importGroupSettings->setApiPropertyIds(null);
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::NONE);
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findOneBy(
            ['rent' => '2100.00', 'status' => ContractStatus::APPROVED]
        );
        $this->assertNotEmpty($contract, 'We should have such contract in fixtures');
        $contract->setExternalLeaseId(99999999);
        $residentMapping = new ResidentMapping();
        $residentMapping->setResidentId('t0088888');
        $residentMapping->setHolding($contract->getHolding());
        $residentMapping->setTenant($contract->getTenant());
        $this->getEntityManager()->persist($residentMapping);
        $this->getEntityManager()->flush();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('import_lease_id.csv');
        $attFile->attachFile($filePath);
        $this->setPropertyFirst();
        $submitImportFile = $this->getDomElement('.submitImportFile');
        $submitImportFile->click();
        $this->assertNull($this->page->find('css', '.error_list>li'), 'Error should not be on this page.');
        $this->assertNotNull($this->page->find('css', 'table'), 'We should see mapping table.');

        $mapFile = $this->mapFile;
        $mapFile[15] = ImportMapping::KEY_TENANT_STATUS;
        $mapFile[16] = ImportMapping::KEY_EXTERNAL_LEASE_ID;

        $this->fillCsvMapping($mapFile, 16);

        $submitImportFile->click();
        $this->session->wait(1000, "$('table').is(':visible')");
        $trs = $this->getParsedTrsByStatus();

        $this->assertCount(2, $trs, "Count statuses is wrong");
        $this->assertCount(1, $trs['import.status.new'], "Count contract with status 'new' wrong");
        $this->assertCount(1, $trs['import.status.match'], "Count contract with status 'match' wrong");
        $submitImportFile->click();
        $this->waitRedirectToSummaryPage();
        $this->getEntityManager()->clear();
        $contractUpdated = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findOneBy(
            ['rent' => '777989.00', 'status' => ContractStatus::APPROVED]
        );
        $this->assertNotEmpty($contractUpdated, 'We should update exist contract per lease id');
        $contractNew = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findOneBy(
            ['externalLeaseId' => '123456789']
        );
        $this->assertNotEmpty($contractNew, 'We should insert new contract');
    }

    /**
     * @test
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function mriBostonPostImport()
    {
        $this->load(true);

        $importGroupSettings = $this->getImportGroupSettings();
        $this->assertNotEmpty($importGroupSettings, 'We do not have correct settings in fixtures');
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::SINGLE_PROPERTY);
        $importGroupSettings->setApiPropertyIds(null);
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::MRI_BOSTONPOST);
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findOneBy(
            ['rent' => '2100.00', 'status' => ContractStatus::APPROVED]
        );
        $this->assertNotEmpty($contract, 'We should have such contract in fixtures');
        $contract->setExternalLeaseId(99999999);
        $this->getEntityManager()->flush();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('import_lease_id_mri_bostonpost.csv');
        $attFile->attachFile($filePath);
        $this->setPropertyFirst();
        $submitImportFile = $this->getDomElement('.submitImportFile');
        $submitImportFile->click();
        $this->assertNull($this->page->find('css', '.error_list>li'), 'Error should not be on this page.');
        $this->assertNotNull($this->page->find('css', 'table'), 'We should see mapping table.');

        $mapFile = $this->mapFile;
        unset($mapFile[4]); // removed resident ID mapping
        $mapFile[15] = ImportMapping::KEY_TENANT_STATUS;
        $mapFile[16] = ImportMapping::KEY_EXTERNAL_LEASE_ID;

        $this->fillCsvMapping($mapFile, 16);

        $submitImportFile->click();
        $this->session->wait(1000, "$('table').is(':visible')");
        $trs = $this->getParsedTrsByStatus();

        $this->assertCount(1, $trs, "Count statuses is wrong");
        $this->assertCount(2, $trs['import.status.new'], "Count contract with status 'new' wrong");
        $submitImportFile->click();
        $this->waitRedirectToSummaryPage();
        $this->getEntityManager()->clear();
        $contractUpdated = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findOneBy(
            ['rent' => '777989.00', 'status' => ContractStatus::APPROVED]
        );
        $this->assertNotEmpty($contractUpdated, 'We should update exist contract per lease id');
        $contractNew = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findOneBy(
            ['externalLeaseId' => '123456789']
        );
        $this->assertNotEmpty($contractNew, 'We should insert new contract');

        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFixtureFilePathByName('import_lease_id_mri_bostonpost.csv');
        $attFile->attachFile($filePath);
        $this->setPropertyFirst();
        $submitImportFile = $this->getDomElement('.submitImportFile');
        $submitImportFile->click();
        $this->assertNull($this->page->find('css', '.error_list>li'), 'Error should not be on this page.');
        $this->assertNotNull($this->page->find('css', 'table'), 'We should see mapping table.');

        $this->fillCsvMapping($mapFile, 16);

        $submitImportFile->click();
        $this->session->wait(1000, "$('table').is(':visible')");
        $trs = $this->getParsedTrsByStatus();

        $this->assertCount(1, $trs, "Count statuses is wrong");
        $this->assertCount(2, $trs['import.status.match'], "Count contract with status 'match' wrong");
    }
}
