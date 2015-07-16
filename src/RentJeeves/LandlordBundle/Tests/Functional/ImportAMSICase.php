<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;

class ImportAMSICase extends ImportBaseAbstract
{
    /**
     * @test
     */
    public function shouldImportAMSI()
    {
        $this->markTestSkipped(
            'AMSI Return: ' .
            'An error occured while creating webservice objects to start the process. ' .
            'Please verify portfolio name. There is no row at position 0.'
        );
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $em = $this->getEntityManager();
        /** @var $landlord Landlord */
        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneByEmail('landlord1@example.com');
        $holding = $landlord->getHolding();
        $holding->setApiIntegrationType(ApiIntegrationType::AMSI);
        $em->flush($holding);
        $contracts = $em->getRepository('RjDataBundle:Contract')->findAll();
        // We must make sure the data saved into DB, so we count before import and after
        $this->assertEquals(23, count($contracts));
        $contractsWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertEquals(1, count($contractsWaiting));

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($submitImport = $this->page->find('css', '.submitImportFile'));
        $this->assertNotNull($source = $this->page->findAll('css', '.radio'));
        $source[1]->click();
        $this->assertNotNull($propertyId = $this->page->find('css', '#import_file_type_propertyId'));
        $propertyId->setValue('001');
        $submitImport->click();

        $this->session->wait(
            80000,
            "$('table').is(':visible')"
        );
        $this->waitReviewAndPost();
        for ($i = 0; $i <= 2; $i++) {
            $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
            $submitImportFile->click();
            $this->waitReviewAndPost();
        }

        // We must make sure the data saved into DB, so we count before import and after
        $contracts = $em->getRepository('RjDataBundle:Contract')->findAll();
        $this->assertGreaterThan(23, count($contracts));
        $contractsWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertGreaterThan(1, count($contractsWaiting)); // by @tobur
    }
}
