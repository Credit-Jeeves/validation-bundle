<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport\Transformer;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\ImportBundle\PropertyImport\Transformer\MRITransformer;
use RentJeeves\ImportBundle\PropertyImport\Transformer\TransformerFactory;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class TransformerFactoryCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @var string
     */
    protected $tmpFile;

    protected function createCustomTransformerFile()
    {
        $customFileDist = __DIR__ . '/../../../../PropertyImport/Transformer/Custom/ExampleCustomTransformer.php.dist';
        $this->tmpFile = __DIR__ . '/../../../../PropertyImport/Transformer/Custom/ExampleCustomTransformer.php';
        copy($customFileDist, $this->tmpFile);
    }

    protected function createInvalidCustomTransformerFile()
    {
        $customFileDist = __DIR__ . '/../../../Fixtures/InvalidCustomTransformer.php.dist';
        $this->tmpFile = __DIR__ . '/../../../Fixtures/InvalidCustomTransformer.php';
        copy($customFileDist, $this->tmpFile);
    }

    protected function createCustomTransformerWithInvalidNamespace()
    {
        $customFileDist = __DIR__ . '/../../../Fixtures/CustomTransformerWithInvalidNamespace.php.dist';
        $this->tmpFile = __DIR__ . '/../../../Fixtures/CustomTransformerWithInvalidNamespace.php';
        copy($customFileDist, $this->tmpFile);
    }

    public function tearDown()
    {
        parent::tearDown();

        if (true === file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportException
     * @expectedExceptionMessage Not found any files with name "ExampleCustomTransformer.php"
     */
    public function shouldThrowExceptionIfDbHasRecordForInputDataButFileWithReturnedNameNotFound()
    {
        $group = new Group();

        $repoMock = $this->getImportTransformerRepositoryMock();
        $repoMock->expects($this->once())
            ->method('findClassNameWithPriorityByGroupAndExternalPropertyId')
            ->will($this->returnValue('ExampleCustomTransformer'));
        $factory = new TransformerFactory(
            $repoMock,
            $this->getLoggerMock(),
            [__DIR__ . '/../../../../PropertyImport/Transformer/Custom'],
            [ApiIntegrationType::MRI => $this->getMriTransformerMock()]
        );

        $transformer = $factory->getTransformer($group, 'test');

        $this->assertInstanceOf(
            'RentJeeves\ImportBundle\PropertyImport\Transformer\TransformerInterface',
            $transformer
        );
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportException
     * @expectedExceptionMessage Custom transformer must be instance of "TransformerInterface".
     */
    public function shouldThrowExceptionIfDbHasRecordForInputDataButClassNotImplementInterface()
    {
        $this->createInvalidCustomTransformerFile();

        $group = new Group();

        $repoMock = $this->getImportTransformerRepositoryMock();
        $repoMock->expects($this->once())
            ->method('findClassNameWithPriorityByGroupAndExternalPropertyId')
            ->will($this->returnValue('InvalidCustomTransformer'));
        $factory = new TransformerFactory(
            $repoMock,
            $this->getLoggerMock(),
            [__DIR__ . '/../../../Fixtures'],
            [ApiIntegrationType::MRI => $this->getMriTransformerMock()]
        );

        $factory->getTransformer($group, 'test');
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportException
     * @expectedExceptionMessage Pls check name and namespace in custom file
     */
    public function shouldThrowExceptionIfDbHasRecordForInputDataButClassHaveIncorrectNamespace()
    {
        $this->createCustomTransformerWithInvalidNamespace();

        $group = new Group();

        $repoMock = $this->getImportTransformerRepositoryMock();
        $repoMock->expects($this->once())
            ->method('findClassNameWithPriorityByGroupAndExternalPropertyId')
            ->will($this->returnValue('CustomTransformerWithInvalidNamespace'));
        $factory = new TransformerFactory(
            $repoMock,
            $this->getLoggerMock(),
            [__DIR__ . '/../../../Fixtures'],
            [ApiIntegrationType::MRI => $this->getMriTransformerMock()]
        );

        $factory->getTransformer($group, 'test');
    }

    /**
     * @test
     */
    public function shouldCreateCustomTransformerIfDbHasRecordForInputData()
    {
        $this->createCustomTransformerFile();

        $group = new Group();

        $repoMock = $this->getImportTransformerRepositoryMock();
        $repoMock->expects($this->once())
            ->method('findClassNameWithPriorityByGroupAndExternalPropertyId')
            ->will($this->returnValue('ExampleCustomTransformer'));
        $factory = new TransformerFactory(
            $repoMock,
            $this->getLoggerMock(),
            [__DIR__ . '/../../../../PropertyImport/Transformer/Custom'],
            [ApiIntegrationType::MRI => $this->getMriTransformerMock()]
        );

        $transformer = $factory->getTransformer($group, 'test');

        $this->assertInstanceOf(
            'RentJeeves\ImportBundle\PropertyImport\Transformer\TransformerInterface',
            $transformer
        );
    }

    /**
     * @test
     */
    public function shouldReturnMRITransformerIfDbDoesNotHaveRecordForInputData()
    {
        $group = new Group();
        $holding = new Holding();
        $holding->setApiIntegrationType(ApiIntegrationType::MRI);
        $group->setHolding($holding);

        $repoMock = $this->getImportTransformerRepositoryMock();
        $repoMock->expects($this->once())
            ->method('findClassNameWithPriorityByGroupAndExternalPropertyId')
            ->will($this->returnValue(null));
        $factory = new TransformerFactory(
            $repoMock,
            $this->getLoggerMock(),
            [__DIR__ . '/../../../../PropertyImport/Transformer/Custom'],
            [ApiIntegrationType::MRI => $this->getMriTransformerMock()]
        );

        $transformer = $factory->getTransformer($group, 'test');

        $this->assertInstanceOf(
            'RentJeeves\ImportBundle\PropertyImport\Transformer\MRITransformer',
            $transformer
        );
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportException
     * @expectedExceptionMessage Accounting System with name "none" is not supported.
     */
    public function shouldThrowExceptionIfDbDoesNotHaveRecordForInputDataAndHoldingHaveIncorrectApiType()
    {
        $group = new Group();
        $holding = new Holding();
        $holding->setApiIntegrationType('none');
        $group->setHolding($holding);

        $repoMock = $this->getImportTransformerRepositoryMock();
        $repoMock->expects($this->once())
            ->method('findClassNameWithPriorityByGroupAndExternalPropertyId')
            ->will($this->returnValue(null));
        $factory = new TransformerFactory(
            $repoMock,
            $this->getLoggerMock(),
            [__DIR__ . '/../../../../PropertyImport/Transformer/Custom'],
            [ApiIntegrationType::MRI => $this->getMriTransformerMock()]
        );

        $factory->getTransformer($group, 'test');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\DataBundle\Entity\ImportTransformerRepository
     */
    protected function getImportTransformerRepositoryMock()
    {
        return $this->getBaseMock('\RentJeeves\DataBundle\Entity\ImportTransformerRepository');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MRITransformer
     */
    protected function getMriTransformerMock()
    {
        return $this->getBaseMock('\RentJeeves\ImportBundle\PropertyImport\Transformer\MRITransformer');
    }
}
