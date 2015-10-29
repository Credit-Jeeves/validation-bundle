<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\ExternalApiBundle\Tests\Services\ResMan\ResManClientCase;

class ImportResManCase extends ImportBaseAbstract
{
    /**
     * @test
     */
    public function resmanBaseImport()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $em = $this->getEntityManager();
        /** @var Landlord $landlord */
        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneByEmail('landlord1@example.com');
        $holding = $landlord->getHolding();
        $holding->setApiIntegrationType(ApiIntegrationType::RESMAN);
        $em->flush($holding);
        $contract = $em->getRepository('RjDataBundle:Contract')->findAll();
        // We must make sure the data saved into DB, so we count before import and after
        $this->assertCount(23, $contract);
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertCount(1, $contractWaiting, 'We should get just one contract from fixtures');

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull(
            $submitImport = $this->page->find('css', '.submitImportFile'),
            'Submit button should exist'
        );
        $this->assertNotNull($source = $this->page->findAll('css', '#import_file_type_fileType_box>.radio'));
        $source[1]->click();
        $this->assertNotNull($propertyId = $this->page->find('css', '#import_file_type_propertyId'));
        $propertyId->setValue(ResManClientCase::EXTERNAL_PROPERTY_ID);
        $submitImport->click();

        $this->session->wait(
            80000,
            "$('table').is(':visible')"
        );
        //First page
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        //Second page
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
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
