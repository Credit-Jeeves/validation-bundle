<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\DataBundle\Enum\ImportType;
use RentJeeves\ExternalApiBundle\Tests\Services\ResMan\ResManClientCase;

class ImportResManCase extends ImportBaseAbstract
{
    /**
     * @test
     */
    public function resmanBaseImport()
    {
        $this->load(true);
        // prepare fixtures
        $em = $this->getEntityManager();
        $propertyMapping = $em->getRepository('RjDataBundle:PropertyMapping')->findOneBy(
            ['externalPropertyId' => 'rnttrk01']
        );
        $this->assertNotEmpty($propertyMapping, 'We don\'t have propertyMapping in fixtures');
        $propertyMapping->setExternalPropertyId(ResManClientCase::EXTERNAL_PROPERTY_ID);
        $em->flush();
        /** @var Landlord $landlord */
        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneByEmail('landlord1@example.com');
        $this->assertNotNull($landlord, 'Check fixtures, landlord with email "landlord1@example.com" should exist');
        $holding = $landlord->getHolding();
        $holding->setAccountingSystem(AccountingSystem::RESMAN);
        /** @var Group $group */
        $group = $em->getRepository('DataBundle:Group')->findOneByName('Test Rent Group');
        $this->assertNotNull($group, 'Check fixtures, group with name "Test Rent Group" should exist');
        $this->assertEquals(
            $holding->getId(),
            $group->getHolding()->getId(),
            'Check fixtures, group with name "Test Rent Group" should belong to holding with id ' . $holding->getId()
        );
        $importSettings = $group->getImportSettings();
        $importSettings->setApiPropertyIds(
            ResManClientCase::EXTERNAL_PROPERTY_ID . ', ' . ResManClientCase::EXTERNAL_PROPERTY_ID
        );
        $importSettings->setImportType(ImportType::MULTI_PROPERTIES);
        $importSettings->setSource(ImportSource::INTEGRATED_API);
        $em->flush();

        // We must make sure the data saved into DB, so we count before import and after
        $contract = $em->getRepository('RjDataBundle:Contract')->findAll();
        $this->assertCount(24, $contract, 'Check fixtures, should be present just 24 contracts on DB');

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $submitImport = $this->getDomElement('.submitImportFile', 'Submit button should exist');
        $submitImport->click();
        $this->session->wait(
            80000,
            "$('table').is(':visible')"
        );
        //1
        $submitImport->click();
        $this->waitReviewAndPost();
        //2
        $submitImport->click();
        $this->waitReviewAndPost();
        //3
        $submitImport->click();
        $this->waitReviewAndPost();
        //4
        $submitImport->click();
        $this->waitReviewAndPost();
        //5
        $submitImport->click();
        $this->waitReviewAndPost();
        //6
        $submitImport->click();
        $this->waitReviewAndPost();
        //7
        $submitImport->click();
        $this->waitReviewAndPost();
        //8
        $submitImport->click();
        $this->waitReviewAndPost();
        //9
        $submitImport->click();
        $this->waitReviewAndPost();
        //10
        $submitImport->click();
        $this->waitReviewAndPost();
        //11
        $submitImport->click();
        $this->waitReviewAndPost();
        //12
        $submitImport->click();
        $this->waitReviewAndPost();
        //13
        $submitImport->click();
        $this->waitReviewAndPost();
        //14
        $submitImport->click();
        $this->waitReviewAndPost();
        //15
        $submitImport->click();
        $this->waitReviewAndPost();
        //16
        $submitImport->click();
        $this->waitReviewAndPost();
        //17
        $submitImport->click();
        $this->waitReviewAndPost();
        //18
        $submitImport->click();
        $this->waitReviewAndPost();
        //19
        $submitImport->click();
        $this->waitReviewAndPost();
        //20
        $submitImport->click();
        $this->waitReviewAndPost();

        $this->waitRedirectToSummaryPage();
        $this->assertNotNull($publicId = $this->page->find('css', '#publicId'));

        // We must make sure the data saved into DB, so we count before import and after
        $contracts = $em->getRepository('RjDataBundle:Contract')->findAll();
        $this->assertGreaterThan(30, count($contracts), 'Contracts should be added');
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            ['externalLeaseId' => ResManClientCase::EXTERNAL_LEASE_ID]
        );
        $this->assertNotEmpty($contract, 'We should update contract');
    }

    /**
     * @test
     * @depends resmanBaseImport
     */
    public function checkByResManRecurringChargeImport()
    {
        $this->setDefaultSession('selenium2');
        $em = $this->getEntityManager();
        /** @var Landlord $landlord */
        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneByEmail('landlord1@example.com');
        $holding = $landlord->getHolding();
        $holding->setUseRecurringCharges(true);
        $em->flush();
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $submitImport = $this->getDomElement('.submitImportFile', 'Submit button should exist');
        $submitImport->click();

        $this->session->wait(
            80000,
            "$('table').is(':visible')"
        );
        //First page
        $rentNotEditable = $this->getDomElements('.rentNotEditable', 'We should show element span with rent');
        $this->assertCount(9, $rentNotEditable, 'All contracts should be matched, so all rent not editable');
        //Reverse check
        $holding = $landlord->getHolding();
        $holding->setUseRecurringCharges(false);
        $em->flush();
        $this->session->reload();
        $this->session->wait(
            80000,
            "$('table').is(':visible')"
        );
        //First page
        $submitImport = $this->getDomElement('.submitImportFile', 'Submit button should exist');
        $submitImport->click();
        $this->waitReviewAndPost();
        $this->assertNull(
            $this->page->find('css', '.rentNotEditable'),
            'We should not see not editable fields'
        );
    }

    /**
     * @test
     * @depends checkByResManRecurringChargeImport
     */
    public function checkOnlyReviewAndPostImport()
    {
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($exceptionOnly = $this->page->find('css', '#import_file_type_onlyException'));
        $exceptionOnly->check();
        $submitImport = $this->getDomElement('.submitImportFile', 'Submit button should exist');
        $submitImport->click();
        $this->waitReview();
        $this->session->wait(20000, "$('.submitImportFile>span').is(':visible')");
        $trs = $this->getParsedTrsByStatus();
        $this->assertCount(1, $trs, "Count statuses is wrong");
        $this->assertCount(2, $trs['import.status.error'], "Count contract with status 'error' wrong");
        $submitImport = $this->getDomElement('.submitImportFile', 'Submit button should exist');
        $submitImport->click();
        $this->waitRedirectToSummaryPage();
        $this->assertNotNull($publicId = $this->page->find('css', '#publicId'));
    }
}
