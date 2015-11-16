<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;

class ImportMRICase extends ImportBaseAbstract
{
    /**
     * @test
     */
    public function mriBaseImport()
    {
        $this->markTestSkipped('Temporarily skip this test due to: need actual contract with filled address field');
        $this->load(true);
        $this->setDefaultSession('selenium2');

        $em = $this->getEntityManager();
        /** @var $landlord Landlord */
        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneByEmail('landlord1@example.com');
        $holding = $landlord->getHolding();
        $holding->setApiIntegrationType(ApiIntegrationType::MRI);
        $em->flush($holding);
        // We must make sure the data saved into DB, so we count before import and after
        $contract = $em->getRepository('RjDataBundle:Contract')->findAll();
        $this->assertCount(23, $contract);
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertCount(1, $contractWaiting);

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($submitImport = $this->page->find('css', '.submitImportFile'));
        $this->setPropertySecond();
        $this->assertNotNull($source = $this->page->findAll('css', '.radio'));
        $source[1]->click();
        $this->assertNotNull($propertyId = $this->page->find('css', '#import_file_type_propertyId'));
        $propertyId->setValue('500');
        $submitImport->click();

        $this->session->wait(
            80000,
            "$('table').is(':visible')"
        );
        $this->waitReviewAndPost();
        for ($i = 0; $i <= 2; $i++) {
            $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));

            if ($i === 0) {
                $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
                $this->assertCount(2, $errorFields);
                $errorFields[0]->setValue('06/01/2008');
                $errorFields[1]->setValue('06/01/2008');
            }

            if ($i === 1 || $i === 2) {
                $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
                $this->assertCount(3, $errorFields);
                $errorFields[0]->setValue('06/01/2008');
                $errorFields[1]->setValue('06/01/2008');
                $errorFields[2]->setValue('06/01/2008');
            }
            $submitImportFile->click();
            $this->waitReviewAndPost();
        }

        $contract = $em->getRepository('RjDataBundle:Contract')->findAll();
        $this->assertCount(25, $contract);
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertCount(10, $contractWaiting);
    }
}
