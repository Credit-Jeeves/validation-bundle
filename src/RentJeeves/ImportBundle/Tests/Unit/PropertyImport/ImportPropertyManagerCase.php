<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Enum\ImportModelType;
use RentJeeves\ImportBundle\PropertyImport\Extractor\ExtractorBuilder;
use RentJeeves\ImportBundle\PropertyImport\Extractor\ExtractorFactory;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces\ExtractorInterface;
use RentJeeves\ImportBundle\PropertyImport\ImportPropertyManager;
use RentJeeves\ImportBundle\PropertyImport\Loader\LoaderFactory;
use RentJeeves\ImportBundle\PropertyImport\Loader\MappedLoader;
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
            $this->getExtractorBuilderMock(),
            $this->getTransformerFactoryMock(),
            $this->getLoaderFactoryMock(),
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

        $extractorMock = $this->getBaseMock(ExtractorInterface::class);
        $extractorMock->expects($this->once())
            ->method('extractData')
            ->will($this->returnValue($extData = ['testKey' => 'testValue']));

        $extractorFactoryMock = $this->getExtractorFactoryMock();
        $extractorFactoryMock->expects($this->once())
            ->method('getExtractor')
            ->with($this->equalTo($group))
            ->will($this->returnValue($extractorMock));

        $extractorBuilder = new ExtractorBuilder($extractorFactoryMock, $this->getLoggerMock());

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

        $propertyLoaderMock = $this->getMappedLoaderMock();
        $propertyLoaderMock->expects($this->once())
            ->method('loadData')
            ->with($this->equalTo($import), $this->equalTo('testExtPropertyId'));

        $loaderFactory = $this->getLoaderFactoryMock();
        $loaderFactory->expects($this->once())
            ->method('getLoader')
            ->willReturn($propertyLoaderMock);

        $propertyManager = new ImportPropertyManager(
            $extractorBuilder,
            $transformerFactoryMock,
            $loaderFactory,
            $this->getLoggerMock()
        );
        $propertyManager->import($import, 'testExtPropertyId');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ExtractorFactory
     */
    protected function getExtractorFactoryMock()
    {
        return $this->getBaseMock(ExtractorFactory::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ExtractorBuilder
     */
    protected function getExtractorBuilderMock()
    {
        return $this->getBaseMock(ExtractorBuilder::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TransformerFactory
     */
    protected function getTransformerFactoryMock()
    {
        return $this->getBaseMock(TransformerFactory::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoaderFactory
     */
    protected function getLoaderFactoryMock()
    {
        return $this->getBaseMock(LoaderFactory::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MappedLoader
     */
    protected function getMappedLoaderMock()
    {
        return $this->getBaseMock(MappedLoader::class);
    }
}
