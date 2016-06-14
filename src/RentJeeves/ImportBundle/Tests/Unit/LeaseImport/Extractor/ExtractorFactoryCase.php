<?php

namespace RentJeeves\ImportBundle\Tests\Unit\LeaseImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\GroupSettings;
use RentJeeves\DataBundle\Entity\ImportGroupSettings;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\ImportBundle\LeaseImport\Extractor\CsvExtractor;
use RentJeeves\ImportBundle\LeaseImport\Extractor\ExtractorFactory;
use RentJeeves\ImportBundle\LeaseImport\Extractor\Interfaces\ApiLeaseExtractorInterface;
use RentJeeves\ImportBundle\LeaseImport\Extractor\Interfaces\CsvLeaseExtractorInterface;
use RentJeeves\ImportBundle\LeaseImport\Extractor\ResmanExtractor;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class ExtractorFactoryCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportInvalidArgumentException
     */
    public function shouldThrowExceptionIfGroupDoesNotHaveSettings()
    {
        $factory = new ExtractorFactory();
        $factory->getExtractor(new Group());
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportInvalidArgumentException
     * @expectedExceptionMessage ExtractorFactory: Accounting System with name "mri" is not supported.
     */
    public function shouldThrowExceptionIfGroupRelatedWithNotSupportedAccountingSystem()
    {
        $groupSettings = new GroupSettings();
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::MRI);

        $group = new Group();
        $group->setGroupSettings($groupSettings);
        $group->setHolding($holding);

        $importSettings = new ImportGroupSettings();
        $importSettings->setSource(ImportSource::INTEGRATED_API);
        $group->setImportSettings($importSettings);

        $factory = new ExtractorFactory();
        $factory->getExtractor($group);
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportInvalidArgumentException
     * @expectedExceptionMessage "yardi genesis v2" is not valid Api Accounting System Name.
     */
    public function shouldThrowExceptionIfTryAddApiExtractorForNotApiAccountingSystem()
    {
        $factory = new ExtractorFactory();
        $factory->addApiExtractor(
            AccountingSystem::YARDI_GENESIS_2,
            $this->getBaseMock(ApiLeaseExtractorInterface::class)
        );
    }

    /**
     * @test
     */
    public function shouldReturnExtractorForValidAndSupportedAccountingSystem()
    {
        $groupSettings = new GroupSettings();
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::RESMAN);

        $group = new Group();
        $group->setGroupSettings($groupSettings);
        $group->setHolding($holding);

        $importSettings = new ImportGroupSettings();
        $importSettings->setSource(ImportSource::INTEGRATED_API);
        $group->setImportSettings($importSettings);

        $factory = new ExtractorFactory();
        $factory->addApiExtractor(AccountingSystem::RESMAN, $this->getBaseMock(ResmanExtractor::class));
        $extractor = $factory->getExtractor($group);

        $this->assertInstanceOf(ApiLeaseExtractorInterface::class, $extractor, 'Factory return incorrect result.');
    }

    /**
     * @test
     */
    public function shouldReturnCsvExtractor()
    {
        $groupSettings = new GroupSettings();

        $group = new Group();
        $group->setGroupSettings($groupSettings);

        $importSettings = new ImportGroupSettings();
        $importSettings->setSource(ImportSource::CSV);
        $group->setImportSettings($importSettings);

        $factory = new ExtractorFactory();
        $factory->setCsvExtractor($this->getBaseMock(CsvExtractor::class));
        $extractor = $factory->getExtractor($group);

        $this->assertInstanceOf(CsvLeaseExtractorInterface::class, $extractor, 'Factory return incorrect result.');
    }
}
