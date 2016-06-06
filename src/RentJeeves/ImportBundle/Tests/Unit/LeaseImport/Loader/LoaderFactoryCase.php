<?php

namespace RentJeeves\ImportBundle\Tests\Unit\LeaseImport\Loader;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\ImportGroupSettings;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\ImportBundle\LeaseImport\Loader\BaseLoader;
use RentJeeves\ImportBundle\LeaseImport\Loader\CsvLoader;
use RentJeeves\ImportBundle\LeaseImport\Loader\LoaderFactory;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class LoaderFactoryCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportInvalidArgumentException
     */
    public function shouldThrowExceptionIfGroupDoesNotHaveSettings()
    {
        $factory = new LoaderFactory($this->getBaseMock(BaseLoader::class), $this->getBaseMock(CsvLoader::class));
        $factory->getLoader(new Group());
    }

    /**
     * @test
     */
    public function shouldReturnBaseLoaderIfGroupHasSettingForApi()
    {
        $group = new Group();
        $importSettings = new ImportGroupSettings();
        $importSettings->setSource(ImportSource::INTEGRATED_API);

        $group->setImportSettings($importSettings);

        $factory = new LoaderFactory($this->getBaseMock(BaseLoader::class), $this->getBaseMock(CsvLoader::class));
        $loader = $factory->getLoader($group);

        $this->assertInstanceOf(BaseLoader::class, $loader);
    }

    /**
     * @test
     */
    public function shouldReturnCsvLoaderIfGroupHasSettingForCsv()
    {
        $group = new Group();
        $importSettings = new ImportGroupSettings();
        $importSettings->setSource(ImportSource::CSV);

        $group->setImportSettings($importSettings);

        $factory = new LoaderFactory($this->getBaseMock(BaseLoader::class), $this->getBaseMock(CsvLoader::class));
        $loader = $factory->getLoader($group);

        $this->assertInstanceOf(CsvLoader::class, $loader);
    }
}
