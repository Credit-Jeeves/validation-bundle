<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\DataBundle\Enum\AccountingSystem;
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
        $yardiExtractor->extractData($group, 'test');
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportExtractorException
     * @expectedExceptionMessage Can`t get data from Yardi for ExternalPropertyId="test". Details: testMessage
     */
    public function shouldThrowImportExtractorExceptionIfResidentDataManagerThrowException()
    {
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::YARDI_VOYAGER);
        $holding->setYardiSettings(new YardiSettings());
        $group = new Group();
        $group->setHolding($holding);

        $dataManager = $this->getYardiResidentDataManagerMock();
        $dataManager->expects($this->once())
            ->method('getResidentTransactions')
            ->with($this->equalTo('test'))
            ->willThrowException(new \Exception('testMessage'));
        $yardiExtractor = new YardiExtractor($dataManager, $this->getLoggerMock());
        $yardiExtractor->extractData($group, 'test');
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

        $dataManager = $this->getYardiResidentDataManagerMock();
        $dataManager->expects($this->once())
            ->method('getResidentTransactions')
            ->with($this->equalTo('test'))
            ->will($this->returnValue($expectedResponse = ['test']));
        $yardiExtractor = new YardiExtractor($dataManager, $this->getLoggerMock());
        $actualResponse = $yardiExtractor->extractData($group, 'test');

        $this->assertEquals($expectedResponse, $actualResponse, 'Incorrect Response from YardiExtractor.');
    }

    /**
     * @test
     */
    public function shouldReturnEmptyResultFromYardiAndLogMessage()
    {
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::YARDI_VOYAGER);
        $holding->setYardiSettings(new YardiSettings());
        $group = new Group();
        $group->setHolding($holding);

        $dataManager = $this->getYardiResidentDataManagerMock();
        $dataManager->expects($this->once())
            ->method('getResidentTransactions')
            ->with($this->equalTo('test'))
            ->will($this->returnValue($expectedResponse = []));
        $logger = $this->getLoggerMock();
        $logger->expects($this->at(1))
            ->method('info')
            ->with($this->equalTo('Returned response for extPropertyId#test is empty.'));

        $yardiExtractor = new YardiExtractor($dataManager, $logger);
        $actualResponse = $yardiExtractor->extractData($group, 'test');

        $this->assertEquals($expectedResponse, $actualResponse, 'Incorrect Response from YardiExtractor.');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\ExternalApiBundle\Services\Yardi\ResidentDataManager
     */
    protected function getYardiResidentDataManagerMock()
    {
        return $this->getBaseMock('\RentJeeves\ExternalApiBundle\Services\Yardi\ResidentDataManager');
    }
}
