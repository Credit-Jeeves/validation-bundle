<?php

namespace RentJeeves\ImportBundle\Tests\Unit\LeaseImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\ResManSettings;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\ExternalApiBundle\Services\ResMan\ResidentDataManager;
use RentJeeves\ImportBundle\LeaseImport\Extractor\ResmanExtractor;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class ResmanExtractorCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportExtractorException
     * @expectedExceptionMessage Group has incorrect settings for ResmanExtractor.
     */
    public function shouldThrowImportExtractorExceptionIfGroupHasIncorrectSettings()
    {
        $holding = new Holding();
        $group = new Group();
        $group->setHolding($holding);

        $resmanExtractor = new ResmanExtractor($this->getResmanResidentDataManagerMock(), $this->getLoggerMock());
        $resmanExtractor->setGroup($group);
        $resmanExtractor->setExternalPropertyId('test');
        $resmanExtractor->extractData();
    }

    /**
     * @test
     */
    public function shouldReturnDataFromResman()
    {
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::RESMAN);
        $holding->setResmanSettings(new ResmanSettings());
        $group = new Group();
        $group->setHolding($holding);

        $dataManager = $this->getResmanResidentDataManagerMock();
        $dataManager->expects($this->once())
            ->method('getResidentTransactions')
            ->with($this->equalTo('test'))
            ->will($this->returnValue($expectedResponse = ['test']));
        $resmanExtractor = new ResmanExtractor($dataManager, $this->getLoggerMock());
        $resmanExtractor->setGroup($group);
        $resmanExtractor->setExternalPropertyId('test');
        $actualResponse = $resmanExtractor->extractData();

        $this->assertEquals($expectedResponse, $actualResponse, 'Incorrect Response from ResmanExtractor.');
    }

    /**
     * @test
     */
    public function shouldReturnEmptyResultFromResmanAndLogMessage()
    {
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::RESMAN);
        $holding->setResmanSettings(new ResManSettings());
        $group = new Group();
        $group->setHolding($holding);

        $dataManager = $this->getResmanResidentDataManagerMock();
        $dataManager->expects($this->once())
            ->method('getResidentTransactions')
            ->with($this->equalTo('test'))
            ->will($this->returnValue($expectedResponse = []));
        $logger = $this->getLoggerMock();
        $logger->expects($this->at(1))
            ->method('info')
            ->with($this->equalTo('Returned response is empty.'));

        $resmanExtractor = new ResmanExtractor($dataManager, $logger);
        $resmanExtractor->setGroup($group);
        $resmanExtractor->setExternalPropertyId('test');
        $actualResponse = $resmanExtractor->extractData();

        $this->assertEquals($expectedResponse, $actualResponse, 'Incorrect Response from ResmanExtractor.');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ResidentDataManager
     */
    protected function getResmanResidentDataManagerMock()
    {
        return $this->getBaseMock(ResidentDataManager::class);
    }
}
