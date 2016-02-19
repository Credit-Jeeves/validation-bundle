<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\ImportGroupSettings;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetPropertyConfigurationsResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Property;
use RentJeeves\ImportBundle\PropertyImport\ImportPropertySettingsProvider;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class ImportPropertySettingsProviderCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;
    use WriteAttributeExtensionTrait;

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportLogicException
     * @expectedExceptionMessage Function "provideExternalPropertyIds" doesn`t support AccountingSystem "test".
     */
    public function shouldThrowExceptionIfGroupHasNotSupportedAccountingSystem()
    {
        $holding = new Holding();
        $holding->setAccountingSystem('test');
        $group = new Group();
        $group->setHolding($holding);
        $importSettings = new ImportGroupSettings();
        $importSettings->setApiPropertyIds(' test1,  test2 , test3'); //space for check
        $group->setImportSettings($importSettings);

        $importPropertySettingsProvider = new ImportPropertySettingsProvider(
            $this->getBaseMock('\RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient'),
            $this->getLoggerMock()
        );

        $importPropertySettingsProvider->provideExternalPropertyIds($group);
    }

    /**
     * @return array
     */
    public function dataProviderForDb()
    {
        return [
            [AccountingSystem::AMSI],
            [AccountingSystem::MRI],
            [AccountingSystem::RESMAN],
            [AccountingSystem::YARDI_VOYAGER],
        ];
    }

    /**
     * @param string $accountingSystem
     *
     * @test
     * @dataProvider dataProviderForDb
     */
    public function shouldProvideExtPropertyIdsFromDb($accountingSystem)
    {
        $holding = new Holding();
        $holding->setAccountingSystem($accountingSystem);
        $group = new Group();
        $group->setHolding($holding);
        $importSettings = new ImportGroupSettings();
        $importSettings->setApiPropertyIds(' test1,  test2 , test3'); //space for check
        $group->setImportSettings($importSettings);

        $importPropertySettingsProvider = new ImportPropertySettingsProvider(
            $this->getBaseMock('\RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient'),
            $this->getLoggerMock()
        );

        $result = $importPropertySettingsProvider->provideExternalPropertyIds($group);
        $this->assertEquals(3, count($result), 'Provider returned incorrect result.');
        $this->assertEquals('test1', $result[0], 'Provider returned incorrect 1st property.');
        $this->assertEquals('test2', $result[1], 'Provider returned incorrect 2nd property.');
        $this->assertEquals('test3', $result[2], 'Provider returned incorrect 3rd property.');
    }

    /**
     * @test
     */
    public function shouldProvideExtPropertyIdsFromYardiApi()
    {
        $holding = new Holding();
        $holding->setYardiSettings(new YardiSettings());
        $holding->setAccountingSystem(AccountingSystem::YARDI_VOYAGER);
        $group = new Group();
        $group->setHolding($holding);
        $importSettings = new ImportGroupSettings();
        $importSettings->setApiPropertyIds(ImportPropertySettingsProvider::YARDI_ALL_EXTERNAL_PROPERTY_IDS);
        $group->setImportSettings($importSettings);

        $response = new GetPropertyConfigurationsResponse();
        $property = new Property();
        $property->setCode('test');
        $response->setProperty($property);

        $client = $this->getBaseMock('\RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient');
        $client->expects($this->once())
            ->method('getPropertyConfigurations')
            ->will($this->returnValue($response));

        $importPropertySettingsProvider = new ImportPropertySettingsProvider(
            $client,
            $this->getLoggerMock()
        );

        $result = $importPropertySettingsProvider->provideExternalPropertyIds($group);
        $this->assertEquals(1, count($result), 'Provider returned incorrect result.');
        $this->assertEquals('test', $result[0], 'Provider returned incorrect 1st property.');
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportLogicException
     * @expectedExceptionMessage U can`t run Yardi import for Group#1 without YardiSettings.
     */
    public function shouldThrowExceptionForYardiGroupWithoutYardiSettingsIfGetIdsFromApi()
    {
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::YARDI_VOYAGER);
        $group = new Group();
        $this->writeIdAttribute($group, 1);
        $group->setHolding($holding);
        $importSettings = new ImportGroupSettings();
        $importSettings->setApiPropertyIds(ImportPropertySettingsProvider::YARDI_ALL_EXTERNAL_PROPERTY_IDS);
        $group->setImportSettings($importSettings);

        $response = new GetPropertyConfigurationsResponse();
        $property = new Property();
        $property->setCode('test');
        $response->setProperty($property);

        $importPropertySettingsProvider = new ImportPropertySettingsProvider(
            $this->getBaseMock('\RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient'),
            $this->getLoggerMock()
        );

        $importPropertySettingsProvider->provideExternalPropertyIds($group);
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportLogicException
     * @expectedExceptionMessage Yardi import is failed, pls check YardiSettings. Details : test
     */
    public function shouldThrowExceptionIfSendFailedSoapRequest()
    {
        $holding = new Holding();
        $holding->setYardiSettings(new YardiSettings());
        $holding->setAccountingSystem(AccountingSystem::YARDI_VOYAGER);
        $group = new Group();
        $group->setHolding($holding);
        $importSettings = new ImportGroupSettings();
        $importSettings->setApiPropertyIds(ImportPropertySettingsProvider::YARDI_ALL_EXTERNAL_PROPERTY_IDS);
        $group->setImportSettings($importSettings);

        $response = new GetPropertyConfigurationsResponse();
        $property = new Property();
        $property->setCode('test');
        $response->setProperty($property);

        $client = $this->getBaseMock('\RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient');
        $client->expects($this->once())
            ->method('getPropertyConfigurations')
            ->will($this->throwException(new \SoapFault('MustUnderstand', 'test')));

        $importPropertySettingsProvider = new ImportPropertySettingsProvider(
            $client,
            $this->getLoggerMock()
        );

        $importPropertySettingsProvider->provideExternalPropertyIds($group);
    }
}
