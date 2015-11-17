<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\DataBundle\Enum\ImportType;

class ImportAMSICase extends ImportBaseAbstract
{
    /**
     * @test
     */
    public function shouldImportAMSI()
    {
        $this->load(true);
        // prepare fixtures
        $em = $this->getEntityManager();
        /** @var Landlord $landlord */
        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneByEmail('landlord1@example.com');
        $this->assertNotNull($landlord, 'Check fixtures, landlord with email "landlord1@example.com" should exist');
        $holding = $landlord->getHolding();
        $holding->setApiIntegrationType(ApiIntegrationType::AMSI);
        /** @var Group $group */
        $group = $em->getRepository('DataBundle:Group')->findOneByName('Test Rent Group');
        $this->assertNotNull($group, 'Check fixtures, group with name "Test Rent Group" should exist');
        $this->assertEquals(
            $holding->getId(),
            $group->getHolding()->getId(),
            'Check fixtures, group with name "Test Rent Group" should belong to holding with id ' . $holding->getId()
        );
        $importSettings = $group->getImportSettings();
        $importSettings->setApiPropertyIds('001, 002');
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
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $submitImport = $this->getDomElement('.submitImportFile', 'Submit button should exist');
        $submitImport->click();
        $this->session->wait(
            80000,
            "$('table').is(':visible')"
        );
        $this->waitReviewAndPost();
        //First page
        $submitImport->click();
        $this->waitReviewAndPost();
        //Second page
        $submitImport->click();
        $this->waitReviewAndPost();
        //Third page
        $submitImport->click();
        $this->waitReviewAndPost();

        // We must make sure the data saved into DB, so we count before import and after
        $contracts = $em->getRepository('RjDataBundle:Contract')->findAll();
        $this->assertGreaterThan(23, count($contracts));
        $contractsWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertGreaterThan(1, count($contractsWaiting)); // by @tobur
    }
}
