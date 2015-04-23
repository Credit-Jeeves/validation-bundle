<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\AccountingSettings;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;

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
                $errorFields[3]->setValue('CorrrectName');
            }
            if ($i === 2) {
                $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
                $errorFields[4]->setValue('CorrrectName');
            }
            $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
            $submitImportFile->click();
            $this->waitReviewAndPost();
        }

        // We must make sure the data saved into DB, so we count before import and after
        $contracts = $em->getRepository('RjDataBundle:Contract')->findAll();
        $this->assertEquals(28, count($contracts));
        $contractsWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertEquals(20, count($contractsWaiting));
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            array(
                'externalLeaseId' => 'a0668dcf-045d-4183-926c-b7d50a571506',
            )
        );
        $this->assertNotEmpty($contract);
    }
}
