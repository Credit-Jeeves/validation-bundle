<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\ExternalApiBundle\Model\Yardi\FullResident;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Property;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Customer;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\CustomerAddress;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Customers;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionPropertyCustomer;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionProperty;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionUnit;
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
        $yardiExtractor->setExtPropertyId('test');
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
        $yardiExtractor->setExtPropertyId('test');
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
            ->method('getResidentTransactions')
            ->willReturn([]);

        $yardiExtractor = new YardiExtractor($dataManager, $this->getLoggerMock());
        $yardiExtractor->setExtPropertyId('test');
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
        $residentTransactionProperty = $this->getFakeResidentTransactionPropertyCustomer();

        $dataManager = $this->getYardiResidentDataManagerMock();
        $dataManager->expects($this->once())
            ->method('getProperties')
            ->willReturn([$property]);
        $dataManager->expects($this->once())
            ->method('getResidentTransactions')
            ->willReturn([$residentTransactionProperty]);

        $yardiExtractor = new YardiExtractor($dataManager, $this->getLoggerMock());
        $yardiExtractor->setExtPropertyId('test');
        $yardiExtractor->setGroup($group);
        $response = $yardiExtractor->extractData();
        $this->assertCount(1, $response, 'Incorrect Response from YardiExtractor.');
        $residentTransactionProperty = reset($response);
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Model\Yardi\FullResident',
            $residentTransactionProperty,
            'Incorrect class inside response'
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\ExternalApiBundle\Services\Yardi\ResidentDataManager
     */
    protected function getYardiResidentDataManagerMock()
    {
        return $this->getBaseMock('\RentJeeves\ExternalApiBundle\Services\Yardi\ResidentDataManager');
    }

    /**
     * @return ResidentTransactionPropertyCustomer
     */
    protected function getFakeResidentTransactionPropertyCustomer()
    {
        $propertyCustomer = new ResidentTransactionPropertyCustomer();
        $customers = new Customers();
        $customers->addCustomer($customer = new Customer());
        $propertyCustomer->setUnit($unit = new ResidentTransactionUnit());
        $unit->setUnitId('Test');
        $propertyCustomer->setCustomers($customers);
        $customer->setCustomerAddress(new CustomerAddress());
        $customer->getCustomerAddress()->setCustomerAddress1('Fishermans Dr');
        return $propertyCustomer;
    }
}
