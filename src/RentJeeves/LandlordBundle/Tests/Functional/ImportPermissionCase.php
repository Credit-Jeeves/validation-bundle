<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\ImportGroupSettings;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\DataBundle\Enum\ImportType;

class ImportPermissionCase extends ImportBaseAbstract
{
    /**
     * @test
     */
    public function shouldSeeImportWhenHaveCorrectSetting()
    {
        $this->load(true);
        /** @var ImportGroupSettings $importGroupSettings */
        $importGroupSettings = $this->getEntityManager()->getRepository('RjDataBundle:ImportGroupSettings')->findOneBy(
            [
                'source' => 'integrated_api'
            ]
        );

        $importGroupSettings->getGroup()->getHolding()->setApiIntegrationType(ApiIntegrationType::NONE);
        $this->assertNotEmpty($importGroupSettings, 'We do not have correct settings in fixtures');
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::MULTI_GROUPS);
        $this->getEntityManager()->flush();
        $this->setDefaultSession('goutte');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->getDomElement('.submitImportFile', 'Submit button should exist');
        $this->logout();
        $importGroupSettings->getGroup()->getHolding()->setApiIntegrationType(ApiIntegrationType::YARDI_VOYAGER);
        $importGroupSettings->setSource(ImportSource::INTEGRATED_API);
        $this->getEntityManager()->flush();
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->getDomElement('.submitImportFile', 'Submit button should exist');
    }

    /**
     * @test
     */
    public function shouldNotSeeImportWhenHaveIncorrectSetting()
    {
        $this->load(true);
        /** @var ImportGroupSettings $importGroupSettings */
        $importGroupSettings = $this->getEntityManager()->getRepository('RjDataBundle:ImportGroupSettings')->findOneBy(
            [
                'source' => 'integrated_api'
            ]
        );

        $importGroupSettings->getGroup()->getHolding()->setApiIntegrationType(ApiIntegrationType::AMSI);
        $this->assertNotEmpty($importGroupSettings, 'We do not have correct settings in fixtures');
        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::MULTI_GROUPS);
        $this->getEntityManager()->flush();
        $this->setDefaultSession('goutte');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->assertNull($this->page->find('css', '.submitImportFile'), 'Submit button should not exist');
    }
}

