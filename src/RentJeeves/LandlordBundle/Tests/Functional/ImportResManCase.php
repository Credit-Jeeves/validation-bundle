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
        $this->assertCount(23, $contract);
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertCount(1, $contractWaiting);

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($submitImport = $this->page->find('css', '.submitImportFile'));
        $this->assertNotNull($source = $this->page->findAll('css', '#import_file_type_fileType_box>.radio'));
        $source[1]->click();
        $this->assertNotNull($propertyId = $this->page->find('css', '#import_file_type_propertyId'));
        $propertyId->setValue('B342E58C-F5BA-4C63-B050-CF44439BB37D');
        $submitImport->click();

        $this->session->wait(
            80000,
            "$('table').is(':visible')"
        );
        $this->waitReviewAndPost();
        //First page
        $this->assertNotNull(
            $lastName = $this->page->find('css', '.errorField.import_new_user_with_contract_tenant_last_name')
        );
        $lastName->setValue('CorrrectName');
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        //Second page
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        //Third page
        $this->assertNotNull(
            $firstName = $this->page->find('css', '.errorField.import_new_user_with_contract_tenant_first_name')
        );
        $firstName->setValue('CorrrectName');
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost();

        // We must make sure the data saved into DB, so we count before import and after
        $contracts = $em->getRepository('RjDataBundle:Contract')->findAll();
        $this->assertCount(28, $contracts);
        $contractsWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertCount(22, $contractsWaiting);
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            ['externalLeaseId' => 'a0668dcf-045d-4183-926c-b7d50a571506']
        );
        $this->assertNotEmpty($contract);
    }
}
