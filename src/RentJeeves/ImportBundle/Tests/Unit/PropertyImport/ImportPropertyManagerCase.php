<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Enum\ImportModelType;
use RentJeeves\ImportBundle\PropertyImport\Extractor\ExtractorFactory;
use RentJeeves\ImportBundle\PropertyImport\ImportPropertyManager;
use RentJeeves\ImportBundle\PropertyImport\Loader\PropertyLoader;
use RentJeeves\ImportBundle\PropertyImport\Transformer\TransformerFactory;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class ImportPropertyManagerCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportLogicException
     */
    public function shouldThrowExceptionIfImportPropertyManagerUsingForContractImport()
    {
        $import = new Import();
        $import->setGroup(new Group());
        $import->setImportType(ImportModelType::CONTRACT);

        $propertyManager = new ImportPropertyManager(
            $this->getExtractorFactoryMock(),
            $this->getTransformerFactoryMock(),
            $this->getPropertyLoaderMock(),
            $this->getLoggerMock()
        );
        $propertyManager->import($import, 'test');
    }

    /**
     * @test
     */
    public function shouldCallAllServiceForImportCorrectData()
    {
        $holding = new Holding();
        $holding->setAccountingSystem('testType');

        $group = new Group();
        $group->setHolding($holding);

        $import = new Import();
        $import->setGroup($group);
        $import->setImportType(ImportModelType::PROPERTY);

        $extractorMock = $this->getBaseMock('\RentJeeves\ImportBundle\PropertyImport\Extractor\ExtractorInterface');
        $extractorMock->expects($this->once())
            ->method('extractData')
            ->with($this->equalTo($group), $this->equalTo('testExtPropertyId'))
            ->will($this->returnValue($extData = ['testKey' => 'testValue']));

        $extractorFactoryMock = $this->getExtractorFactoryMock();
        $extractorFactoryMock->expects($this->once())
            ->method('getExtractor')
            ->with($this->equalTo('testType'))
            ->will($this->returnValue($extractorMock));

        $transformerMock = $this->getBaseMock(
            '\RentJeeves\ImportBundle\PropertyImport\Transformer\TransformerInterface'
        );
        $transformerMock->expects($this->once())
            ->method('transformData')
            ->with($this->equalTo($extData), $this->equalTo($import));
        $transformerFactoryMock = $this->getTransformerFactoryMock();
        $transformerFactoryMock->expects($this->once())
            ->method('getTransformer')
            ->with($this->equalTo($group), $this->equalTo('testExtPropertyId'))
            ->will($this->returnValue($transformerMock));

        $propertyLoaderMock = $this->getPropertyLoaderMock();
        $propertyLoaderMock->expects($this->once())
            ->method('loadData')
            ->with($this->equalTo($import), $this->equalTo('testExtPropertyId'));

        $propertyManager = new ImportPropertyManager(
            $extractorFactoryMock,
            $transformerFactoryMock,
            $propertyLoaderMock,
            $this->getLoggerMock()
        );
        $propertyManager->import($import, 'testExtPropertyId');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ExtractorFactory
     */
    protected function getExtractorFactoryMock()
    {
        return $this->getBaseMock('\RentJeeves\ImportBundle\PropertyImport\Extractor\ExtractorFactory');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TransformerFactory
     */
    protected function getTransformerFactoryMock()
    {
        return $this->getBaseMock('\RentJeeves\ImportBundle\PropertyImport\Transformer\TransformerFactory');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PropertyLoader
     */
    protected function getPropertyLoaderMock()
    {
        return $this->getBaseMock('\RentJeeves\ImportBundle\PropertyImport\Loader\PropertyLoader');
    }
}
