<?php

namespace RentJeeves\ImportBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\DataBundle\Enum\ImportType;
use RentJeeves\LandlordBundle\Services\ImportSettingsValidator;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class ImportSettingsValidatorCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCheckIsValidImportSettingsWhenSettingsInvalid()
    {
        $this->load(true);
        /** @var ImportGroupSettings $importGroupSettings */
        $importGroupSettings = $this->getEntityManager()->getRepository('RjDataBundle:ImportGroupSettings')->findOneBy(
            ['source' => 'integrated_api']
        );

        $this->assertNotEmpty($importGroupSettings, 'We do not have correct settings in fixtures');
        $importGroupSettings->getGroup()->getHolding()->setApiIntegrationType(ApiIntegrationType::AMSI);

        $importGroupSettings->setSource(ImportSource::CSV);
        $importGroupSettings->setImportType(ImportType::MULTI_GROUPS);
        $this->getEntityManager()->flush();
        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneBy(['name' => 'Test Rent Group']);
        $this->assertNotEmpty($group, 'Group not exist in fixtures');
        /** @var ImportSettingsValidator $importSettingsValidator */
        $importSettingsValidator = $this->getContainer()->get('import.settings.validator');
        $this->assertFalse($importSettingsValidator->isValidImportSettings($group), 'Result should be false');
        $this->assertEquals('import.error.settings_is_wrong', $importSettingsValidator->getErrorMessage());
        $this->getEntityManager()->remove($importGroupSettings);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneBy(['name' => 'Test Rent Group']);
        $this->assertNotEmpty($group, 'Group not exist in fixtures');
        $this->assertFalse($importSettingsValidator->isValidImportSettings($group), 'Result should be false');
        $this->assertEquals('import.error.settings_missing', $importSettingsValidator->getErrorMessage());
    }

    /**
     * @test
     */
    public function shouldCheckIsValidImportSettingsWhenSettingsValid()
    {
        $this->load(true);
        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneBy(['name' => 'Test Rent Group']);
        $this->assertNotEmpty($group, 'Group not exist in fixtures');
        /** @var ImportSettingsValidator $importSettingsValidator */
        $importSettingsValidator = $this->getContainer()->get('import.settings.validator');
        $this->assertTrue(
            $importSettingsValidator->isValidImportSettings($group),
            'Result should be true. Please check fixtures, by default we have valid yardi settings'
        );
        $this->assertEmpty($importSettingsValidator->getErrorMessage(), 'We shouldn\'t have any messages here');
    }
}

