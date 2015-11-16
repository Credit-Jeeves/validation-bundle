<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\DataBundle\Enum\ImportType;

class ImportMRICase extends ImportBaseAbstract
{
    /**
     * @test
     */
    public function mriBaseImport()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $importGroupSettings = $this->getImportGroupSettings();
        $this->assertNotEmpty($importGroupSettings, 'We do not have correct settings in fixtures');
        $importGroupSettings->setSource(ImportSource::INTEGRATED_API);
        $importGroupSettings->setImportType(ImportType::MULTI_PROPERTIES);
        $importGroupSettings->setApiPropertyIds('500,501,503');
        $importGroupSettings->getGroup()->getHolding()->setApiIntegrationType(ApiIntegrationType::MRI);
        $this->getEntityManager()->flush();

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($submitImport = $this->page->find('css', '.submitImportFile'));
        $submitImport->click();

        $this->session->wait(
            1000000,
            "$('table').is(':visible')"
        );
        $this->waitReviewAndPost();

        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertCount(9, $errorFields);
    }
}
