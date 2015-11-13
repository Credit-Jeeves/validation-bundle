<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ImportGroupSettings;
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
}
