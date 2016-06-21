<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\MRISettings;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\ImportBundle\PropertyImport\Extractor\MRIExtractor;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class MRIExtractorCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportExtractorException
     * @expectedExceptionMessage Group has incorrect settings for MRIExtractor.
     */
    public function shouldThrowImportExtractorExceptionIfGroupHasIncorrectSettings()
    {
        $holding = new Holding();
        $group = new Group();
        $group->setHolding($holding);

        $mriExtractor = new MRIExtractor($this->getMRIResidentDataManagerMock(), $this->getLoggerMock());
        $mriExtractor->setGroup($group);
        $mriExtractor->setExternalPropertyId('test');
        $mriExtractor->extractData();
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportExtractorException
     * @expectedExceptionMessage Can`t get data from MRI. Details: testMessage
     */
    public function shouldThrowImportExtractorExceptionIfResidentDataManagerThrowException()
    {
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::MRI);
        $holding->setMriSettings(new MRISettings());
        $group = new Group();
        $group->setHolding($holding);

        $dataManager = $this->getMRIResidentDataManagerMock();
        $dataManager->expects($this->once())
            ->method('getResidentTransactions')
            ->with($this->equalTo('test'))
            ->willThrowException(new \Exception('testMessage'));
        $mriExtractor = new MRIExtractor($dataManager, $this->getLoggerMock());
        $mriExtractor->setGroup($group);
        $mriExtractor->setExternalPropertyId('test');
        $mriExtractor->extractData();
    }

    /**
     * @test
     */
    public function shouldReturnDataFromMri()
    {
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::MRI);
        $holding->setMriSettings(new MRISettings());
        $group = new Group();
        $group->setHolding($holding);

        $dataManager = $this->getMRIResidentDataManagerMock();
        $dataManager->expects($this->once())
            ->method('getResidentTransactions')
            ->with($this->equalTo('test'))
            ->will($this->returnValue($expectedResponse = ['test']));
        $mriExtractor = new MRIExtractor($dataManager, $this->getLoggerMock());
        $mriExtractor->setGroup($group);
        $mriExtractor->setExternalPropertyId('test');
        $actualResponse = $mriExtractor->extractData();

        $this->assertEquals($expectedResponse, $actualResponse, 'Incorrect Response from MRIExtractor.');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\ExternalApiBundle\Services\MRI\ResidentDataManager
     */
    protected function getMRIResidentDataManagerMock()
    {
        return $this->getBaseMock('\RentJeeves\ExternalApiBundle\Services\MRI\ResidentDataManager');
    }
}
