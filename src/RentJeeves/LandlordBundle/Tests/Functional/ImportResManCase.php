<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
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
        /** @var Landlord $landlord */
        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneByEmail('landlord1@example.com');
        $this->assertNotNull($landlord, 'Check fixtures, landlord with email "landlord1@example.com" should exist');
        $holding = $landlord->getHolding();
        $holding->setApiIntegrationType(ApiIntegrationType::RESMAN);
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
        $this->assertCount(23, $contract, 'Check fixtures, should be present just 23 contracts on DB');
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertCount(1, $contractWaiting, 'We should get just one contract waiting from fixtures');

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
        //First page
        $submitImport->click();
        $this->waitReviewAndPost();
        //Second page
        $submitImport->click();
        $this->waitReviewAndPost();

        // We must make sure the data saved into DB, so we count before import and after
        $contracts = $em->getRepository('RjDataBundle:Contract')->findAll();
        $this->assertGreaterThan(30, count($contracts), 'Contracts should be added');
        $contractsWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertGreaterThan(5, count($contractsWaiting), 'Contract waiting should be added');
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
        $this->assertNotNull(
            $submitImport = $this->page->find('css', '.submitImportFile'),
            'Submit button should exist'
        );
        $this->assertNotNull(
            $source = $this->page->findAll('css', '#import_file_type_fileType_box>.radio'),
            'Source radio button should exist'
        );
        $source[1]->click();
        $this->assertNotNull(
            $propertyId = $this->page->find('css', '#import_file_type_propertyId'),
            'Property ID input should exist'
        );
        $propertyId->setValue(ResManClientCase::EXTERNAL_PROPERTY_ID);
        $submitImport->click();

        $this->session->wait(
            80000,
            "$('table').is(':visible')"
        );
        //First page
        $this->assertNotNull(
            $submitImportFile = $this->page->find('css', '.submitImportFile>span'),
            'Submit import button should exist'
        );
        $this->assertNotNull(
            $rentNotEditable = $this->page->findAll('css', '.rentNotEditable'),
            'We should show element span with rent'
        );
        $this->assertCount(9, $rentNotEditable, 'All contracts should be mathced, so all rent not editable');
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
        $this->assertNotNull(
            $submitImportFile = $this->page->find('css', '.submitImportFile>span'),
            'Next button should exist'
        );
        $this->waitReviewAndPost();
        $this->assertNull(
            $this->page->find('css', '.rentNotEditable'),
            'We should not see not editable fields'
        );
    }
}
