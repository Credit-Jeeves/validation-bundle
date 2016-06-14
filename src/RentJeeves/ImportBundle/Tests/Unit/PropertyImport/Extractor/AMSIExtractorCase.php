<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\AMSISettings;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\ImportBundle\PropertyImport\Extractor\AMSIExtractor;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class AMSIExtractorCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportExtractorException
     * @expectedExceptionMessage Group has incorrect settings for AMSIExtractor.
     */
    public function shouldThrowImportExtractorExceptionIfGroupHasIncorrectSettings()
    {
        $holding = new Holding();
        $group = new Group();
        $group->setHolding($holding);

        $amsiExtractor = new AMSIExtractor($this->getAMSIResidentDataManagerMock(), $this->getLoggerMock());
        $amsiExtractor->setGroup($group);
        $amsiExtractor->setExternalPropertyId('test');
        $amsiExtractor->extractData();
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportExtractorException
     * @expectedExceptionMessage Can`t get data from AMSI. Details: testMessage
     */
    public function shouldThrowImportExtractorExceptionIfResidentDataManagerThrowException()
    {
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::AMSI);
        $holding->setAmsiSettings(new AMSISettings());
        $group = new Group();
        $group->setHolding($holding);

        $dataManager = $this->getAMSIResidentDataManagerMock();
        $dataManager->expects($this->once())
            ->method('getResidentTransactions')
            ->with($this->equalTo('test'))
            ->willThrowException(new \Exception('testMessage'));
        $amsiExtractor = new AMSIExtractor($dataManager, $this->getLoggerMock());
        $amsiExtractor->setGroup($group);
        $amsiExtractor->setExternalPropertyId('test');
        $amsiExtractor->extractData();
    }

    /**
     * @test
     */
    public function shouldReturnDataFromAMSI()
    {
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::AMSI);
        $holding->setAmsiSettings(new AMSISettings());
        $group = new Group();
        $group->setHolding($holding);

        $dataManager = $this->getAMSIResidentDataManagerMock();
        $dataManager->expects($this->once())
            ->method('getResidentTransactions')
            ->with($this->equalTo('test'))
            ->will($this->returnValue($expectedResponse = ['test']));
        $amsiExtractor = new AMSIExtractor($dataManager, $this->getLoggerMock());
        $amsiExtractor->setGroup($group);
        $amsiExtractor->setExternalPropertyId('test');
        $actualResponse = $amsiExtractor->extractData();

        $this->assertEquals($expectedResponse, $actualResponse, 'Incorrect Response from AMSIExtractor.');
    }

    /**
     * @test
     */
    public function shouldReturnEmptyResultFromAMSIAndLogMessage()
    {
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::AMSI);
        $holding->setAmsiSettings(new AMSISettings());
        $group = new Group();
        $group->setHolding($holding);
        $dataManager = $this->getAMSIResidentDataManagerMock();
        $dataManager->expects($this->once())
            ->method('getResidentTransactions')
            ->with($this->equalTo('test'))
            ->will($this->returnValue($expectedResponse = []));
        $logger = $this->getLoggerMock();
        $logger->expects($this->at(1))
            ->method('info')
            ->with($this->equalTo('Returned response is empty.'));
        $amsiExtractor = new AMSIExtractor($dataManager, $logger);
        $amsiExtractor->setGroup($group);
        $amsiExtractor->setExternalPropertyId('test');
        $actualResponse = $amsiExtractor->extractData();
        $this->assertEquals($expectedResponse, $actualResponse, 'Incorrect Response from AMSIExtractor.');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\ExternalApiBundle\Services\AMSI\ResidentDataManager
     */
    protected function getAMSIResidentDataManagerMock()
    {
        return $this->getBaseMock('\RentJeeves\ExternalApiBundle\Services\AMSI\ResidentDataManager');
    }
}
