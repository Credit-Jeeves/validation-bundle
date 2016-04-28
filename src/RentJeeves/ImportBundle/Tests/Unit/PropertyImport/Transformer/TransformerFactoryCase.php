<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport\Transformer;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\ImportGroupSettings;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\ImportBundle\PropertyImport\Transformer\CsvTransformer;
use RentJeeves\ImportBundle\PropertyImport\Transformer\MRITransformer;
use RentJeeves\ImportBundle\PropertyImport\Transformer\TransformerFactory;
use RentJeeves\ImportBundle\PropertyImport\Transformer\TransformerInterface;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class TransformerFactoryCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @var array
     */
    protected $tmpFiles = [];

    protected function createCustomTransformerFile()
    {
        $customFileDist = __DIR__ . '/../../../../PropertyImport/Transformer/Custom/ExampleCustomTransformer.php.dist';
        $this->tmpFiles[] = __DIR__ . '/../../../../PropertyImport/Transformer/Custom/ExampleCustomTransformer.php';
        copy($customFileDist, $this->tmpFiles[0]);
    }

    protected function createCustomAndParentTransformersFile()
    {
        $customFileDist = __DIR__ . '/../../../../PropertyImport/Transformer/Custom/ExampleCustomTransformer.php.dist';
        $this->tmpFiles[] = __DIR__ . '/../../../../PropertyImport/Transformer/Custom/ExampleCustomTransformer.php';
        copy($customFileDist, $this->tmpFiles[0]);
        $customFileDist = __DIR__ . '/../../../Fixtures/ChildExampleCustomTransformer.php.dist';
        $this->tmpFiles[] = __DIR__ . '/../../../Fixtures/ChildExampleCustomTransformer.php';
        copy($customFileDist, $this->tmpFiles[1]);
    }

    protected function createInvalidCustomTransformerFile()
    {
        $customFileDist = __DIR__ . '/../../../Fixtures/InvalidCustomTransformer.php.dist';
        $this->tmpFiles[] = __DIR__ . '/../../../Fixtures/InvalidCustomTransformer.php';
        copy($customFileDist, $this->tmpFiles[0]);
    }

    protected function createCustomTransformerWithInvalidNamespace()
    {
        $customFileDist = __DIR__ . '/../../../Fixtures/CustomTransformerWithInvalidNamespace.php.dist';
        $this->tmpFiles[] = __DIR__ . '/../../../Fixtures/CustomTransformerWithInvalidNamespace.php';
        copy($customFileDist, $this->tmpFiles[0]);
    }

    public function tearDown()
    {
        parent::tearDown();

        foreach ($this->tmpFiles as $tmpFile) {
            if (true === file_exists($tmpFile)) {
                unlink($tmpFile);
            }
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
        $groupImportSettings = new ImportGroupSettings();
        $groupImportSettings->setSource(ImportSource::INTEGRATED_API);
        $group->setImportSettings($groupImportSettings);
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::MRI);
        $group->setHolding($holding);

        $repoMock = $this->getImportTransformerRepositoryMock();
        $repoMock->expects($this->once())
            ->method('findClassNameWithPriorityByGroupAndExternalPropertyId')
            ->will($this->returnValue('ExampleCustomTransformer'));

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repoMock));

        $factory = new TransformerFactory(
            $em,
            $this->getLoggerMock(),
            [__DIR__ . '/../../../../PropertyImport/Transformer/Custom'],
            [AccountingSystem::MRI => $this->getMriTransformerMock()],
            $this->geCsvTransformerMock()
        );

        $factory->getTransformer($group, 'test');
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportException
     * @expectedExceptionMessage Custom transformer for this Group must be override
     */
    public function shouldThrowExceptionIfDbHasRecordForInputDataButClassNotOverrideBaseTransformer()
    {
        $this->createInvalidCustomTransformerFile();

        $group = new Group();
        $groupImportSettings = new ImportGroupSettings();
        $groupImportSettings->setSource(ImportSource::INTEGRATED_API);
        $group->setImportSettings($groupImportSettings);
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::MRI);
        $group->setHolding($holding);

        $repoMock = $this->getImportTransformerRepositoryMock();
        $repoMock->expects($this->once())
            ->method('findClassNameWithPriorityByGroupAndExternalPropertyId')
            ->will($this->returnValue('InvalidCustomTransformer'));

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repoMock));

        $factory = new TransformerFactory(
            $em,
            $this->getLoggerMock(),
            [__DIR__ . '/../../../Fixtures'],
            [AccountingSystem::MRI => $this->getMriTransformerMock()],
            $this->geCsvTransformerMock()
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
        $groupImportSettings = new ImportGroupSettings();
        $groupImportSettings->setSource(ImportSource::INTEGRATED_API);
        $group->setImportSettings($groupImportSettings);
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::MRI);
        $group->setHolding($holding);

        $repoMock = $this->getImportTransformerRepositoryMock();
        $repoMock->expects($this->once())
            ->method('findClassNameWithPriorityByGroupAndExternalPropertyId')
            ->will($this->returnValue('CustomTransformerWithInvalidNamespace'));

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repoMock));

        $factory = new TransformerFactory(
            $em,
            $this->getLoggerMock(),
            [__DIR__ . '/../../../Fixtures'],
            [AccountingSystem::MRI => $this->getMriTransformerMock()],
            $this->geCsvTransformerMock()
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
        $groupImportSettings = new ImportGroupSettings();
        $groupImportSettings->setSource(ImportSource::INTEGRATED_API);
        $group->setImportSettings($groupImportSettings);
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::MRI);
        $group->setHolding($holding);

        $repoMock = $this->getImportTransformerRepositoryMock();
        $repoMock->expects($this->once())
            ->method('findClassNameWithPriorityByGroupAndExternalPropertyId')
            ->will($this->returnValue('ExampleCustomTransformer'));

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repoMock));

        $mriTransformer = new MRITransformer($this->getEntityManagerMock(), $this->getLoggerMock());
        $factory = new TransformerFactory(
            $em,
            $this->getLoggerMock(),
            [__DIR__ . '/../../../../PropertyImport/Transformer/Custom'],
            [AccountingSystem::MRI => $mriTransformer],
            $this->geCsvTransformerMock()
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
    public function shouldCreateCustomTransformerIfDbHasRecordAndCustomTransformerOverridesNotBaseTransformer()
    {
        $this->createCustomAndParentTransformersFile();

        $group = new Group();
        $groupImportSettings = new ImportGroupSettings();
        $groupImportSettings->setSource(ImportSource::INTEGRATED_API);
        $group->setImportSettings($groupImportSettings);
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::MRI);
        $group->setHolding($holding);

        $repoMock = $this->getImportTransformerRepositoryMock();
        $repoMock->expects($this->once())
            ->method('findClassNameWithPriorityByGroupAndExternalPropertyId')
            ->will($this->returnValue('ChildExampleCustomTransformer'));

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repoMock));

        $mriTransformer = new MRITransformer($this->getEntityManagerMock(), $this->getLoggerMock());
        $factory = new TransformerFactory(
            $em,
            $this->getLoggerMock(),
            [
                __DIR__ . '/../../../../PropertyImport/Transformer/Custom',
                __DIR__ . '/../../../Fixtures',
            ],
            [AccountingSystem::MRI => $mriTransformer],
            $this->geCsvTransformerMock()
        );

        $transformer = $factory->getTransformer($group, 'test');

        $this->assertInstanceOf(TransformerInterface::class, $transformer);
    }

    /**
     * @test
     */
    public function shouldReturnMRITransformerIfDbDoesNotHaveRecordForInputData()
    {
        $group = new Group();
        $groupImportSettings = new ImportGroupSettings();
        $groupImportSettings->setSource(ImportSource::INTEGRATED_API);
        $group->setImportSettings($groupImportSettings);
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::MRI);
        $group->setHolding($holding);

        $repoMock = $this->getImportTransformerRepositoryMock();
        $repoMock->expects($this->once())
            ->method('findClassNameWithPriorityByGroupAndExternalPropertyId')
            ->will($this->returnValue(null));

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repoMock));

        $factory = new TransformerFactory(
            $em,
            $this->getLoggerMock(),
            [__DIR__ . '/../../../../PropertyImport/Transformer/Custom'],
            [AccountingSystem::MRI => $this->getMriTransformerMock()],
            $this->geCsvTransformerMock()
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
        $groupImportSettings = new ImportGroupSettings();
        $groupImportSettings->setSource(ImportSource::INTEGRATED_API);
        $group->setImportSettings($groupImportSettings);
        $holding = new Holding();
        $holding->setAccountingSystem('none');
        $group->setHolding($holding);

        $repoMock = $this->getImportTransformerRepositoryMock();
        $repoMock->expects($this->once())
            ->method('findClassNameWithPriorityByGroupAndExternalPropertyId')
            ->will($this->returnValue(null));

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repoMock));

        $factory = new TransformerFactory(
            $em,
            $this->getLoggerMock(),
            [__DIR__ . '/../../../../PropertyImport/Transformer/Custom'],
            [AccountingSystem::MRI => $this->getMriTransformerMock()],
            $this->geCsvTransformerMock()
        );

        $factory->getTransformer($group, 'test');
    }

    /**
     * @test
     */
    public function shouldReturnCsvTransformerIfGroupSettingsHaveCsvSource()
    {
        $group = new Group();
        $groupImportSettings = new ImportGroupSettings();
        $groupImportSettings->setSource(ImportSource::CSV);
        $group->setImportSettings($groupImportSettings);
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::PROMAS);
        $group->setHolding($holding);

        $factory = new TransformerFactory(
            $this->getEntityManagerMock(),
            $this->getLoggerMock(),
            [__DIR__ . '/../../../../PropertyImport/Transformer/Custom'],
            [AccountingSystem::MRI => $this->getMriTransformerMock()],
            $this->geCsvTransformerMock()
        );

        $transformer = $factory->getTransformer($group, 'test');
        $this->assertInstanceOf(CsvTransformer::class, $transformer);
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CsvTransformer
     */
    protected function geCsvTransformerMock()
    {
        return $this->getBaseMock(CsvTransformer::class);
    }
}
