<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\ExternalApiBundle\Model\Yardi\UnitInformation;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Property;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\UnitInformationCustomer;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\UnitInformationFullUnit;
use RentJeeves\ImportBundle\PropertyImport\Extractor\YardiExtractor;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class YardiExtractorCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportExtractorException
     * @expectedExceptionMessage Group has incorrect settings for YardiExtractor.
     */
    public function shouldThrowImportExtractorExceptionIfGroupHasIncorrectSettings()
    {
        $holding = new Holding();
        $group = new Group();
        $group->setHolding($holding);

        $yardiExtractor = new YardiExtractor($this->getYardiResidentDataManagerMock(), $this->getLoggerMock());
        $yardiExtractor->setExternalPropertyId('test');
        $yardiExtractor->setGroup($group);
        $yardiExtractor->extractData();
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportExtractorException
     */
    public function shouldThrowImportExtractorExceptionIfGetPropertyConfigurationWillBeEmpty()
    {
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::YARDI_VOYAGER);
        $holding->setYardiSettings(new YardiSettings());
        $group = new Group();
        $group->setHolding($holding);

        $dataManager = $this->getYardiResidentDataManagerMock();
        $dataManager->expects($this->once())
            ->method('getProperties')
            ->willReturn([]);

        $yardiExtractor = new YardiExtractor($dataManager, $this->getLoggerMock());
        $yardiExtractor->setExternalPropertyId('test');
        $yardiExtractor->setGroup($group);
        $yardiExtractor->extractData();
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportExtractorException
     */
    public function shouldThrowImportExtractorExceptionIfGetResidentTransactionsWillBeEmpty()
    {
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::YARDI_VOYAGER);
        $holding->setYardiSettings(new YardiSettings());
        $group = new Group();
        $group->setHolding($holding);
        $property = new Property();
        $property->setCode('test');
        $dataManager = $this->getYardiResidentDataManagerMock();
        $dataManager->expects($this->once())
            ->method('getProperties')
            ->willReturn([$property]);

        $dataManager->expects($this->once())
            ->method('getPropertyCustomerUnits')
            ->willReturn([]);

        $yardiExtractor = new YardiExtractor($dataManager, $this->getLoggerMock());
        $yardiExtractor->setExternalPropertyId('test');
        $yardiExtractor->setGroup($group);
        $yardiExtractor->extractData();
    }

    /**
     * @test
     */
    public function shouldReturnDataFromYardi()
    {
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::YARDI_VOYAGER);
        $holding->setYardiSettings(new YardiSettings());
        $group = new Group();
        $group->setHolding($holding);
        $property = new Property();
        $property->setCode('test');
        $customerUnit = $this->getFakeResidentPropertyCustomerUnit();

        $dataManager = $this->getYardiResidentDataManagerMock();
        $dataManager->expects($this->once())
            ->method('getProperties')
            ->willReturn([$property]);
        $dataManager->expects($this->once())
            ->method('getPropertyCustomerUnits')
            ->willReturn([$customerUnit]);

        $yardiExtractor = new YardiExtractor($dataManager, $this->getLoggerMock());
        $yardiExtractor->setExternalPropertyId('test');
        $yardiExtractor->setGroup($group);

        $response = $yardiExtractor->extractData();
        $this->assertCount(1, $response, 'Incorrect Response from YardiExtractor.');
        /** @var UnitInformation $unitInformation */
        $unitInformation = reset($response);
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Model\Yardi\UnitInformation',
            $unitInformation,
            'Incorrect class inside response'
        );

        $this->assertEquals('Test', $unitInformation->getUnit()->getUnitId());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\ExternalApiBundle\Services\Yardi\ResidentDataManager
     */
    protected function getYardiResidentDataManagerMock()
    {
        return $this->getBaseMock('\RentJeeves\ExternalApiBundle\Services\Yardi\ResidentDataManager');
    }

    /**
     * @return UnitInformationCustomer
     */
    protected function getFakeResidentPropertyCustomerUnit()
    {
        $unit = new UnitInformationFullUnit();
        $unit->setUnitId('Test');
        $unitCustomer = new UnitInformationCustomer();

        $unitCustomer->setCustomerId('fake_test_customer_id');
        $unitCustomer->setUnit($unit);

        return $unitCustomer;
    }
}
