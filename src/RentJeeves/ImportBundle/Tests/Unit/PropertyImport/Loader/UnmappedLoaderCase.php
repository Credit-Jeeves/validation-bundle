<?php
namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport\Loader;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\CoreBundle\Services\PropertyManager;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\DataBundle\Entity\ImportPropertyRepository;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Entity\UnitMappingRepository;
use RentJeeves\DataBundle\Enum\ImportPropertyStatus;
use RentJeeves\ImportBundle\PropertyImport\Loader\UnmappedLoader;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class UnmappedLoaderCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;
    use WriteAttributeExtensionTrait;

    /**
     * @test
     */
    public function shouldSetErrorStatusForImportPropertyIfItHasInvalidAddress()
    {
        $group = new Group();
        $this->writeIdAttribute($group, 1);

        $import = new Import();
        $this->writeIdAttribute($import, 1);
        $import->setGroup($group);

        $importProperty = new ImportProperty();
        $importProperty->setImport($import);

        $iterableResultFromDb = $this->getBaseMock('\Doctrine\ORM\Internal\Hydration\IterableResult');
        $iterableResultFromDb->expects($this->exactly(2))
            ->method('next')
            ->will($this->onConsecutiveCalls([$importProperty], false));

        $repositoryMock = $this->getImportPropertyRepositoryMock();
        $repositoryMock->expects($this->once())
            ->method('getNotProcessedImportProperties')
            ->with($this->equalTo($import), $this->equalTo(null))
            ->will($this->returnValue($iterableResultFromDb));

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:ImportProperty'))
            ->will($this->returnValue($repositoryMock));
        $emMock->expects($this->once())
            ->method('flush')
            ->with($this->equalTo($importProperty));

        $loader = new UnmappedLoader(
            $emMock,
            $this->getPropertyManagerMock(),
            $this->getValidatorMock(),
            $this->getLoggerMock()
        );
        $loader->loadData($import);

        $this->assertEquals(ImportPropertyStatus::ERROR, $importProperty->getStatus(), 'Status is not updated');
        $this->assertContains(
            'Address is invalid',
            current($importProperty->getErrorMessages()),
            'Message is not updated'
        );
        $this->assertTrue($importProperty->isProcessed(), 'Status of process is not updated');
    }

    /**
     * @test
     */
    public function shouldCreateNewSinglePropertyAndSetCorrectStatusForImportProperty()
    {
        $holding = new Holding();
        $this->writeIdAttribute($holding, 1);

        $group = new Group();
        $this->writeIdAttribute($group, 1);
        $group->setHolding($holding);

        $import = new Import();
        $this->writeIdAttribute($import, 1);
        $import->setGroup($group);

        $importProperty = new ImportProperty();
        $importProperty->setImport($import);
        $importProperty->setAllowMultipleProperties(false);
        $importProperty->setAddressHasUnits(false);
        $importProperty->setExternalUnitId('test_ext__unit_id');

        $propertyAddress = new PropertyAddress();
        $property = new Property();
        $property->setPropertyAddress($propertyAddress);

        $iterableResultFromDb = $this->getBaseMock('\Doctrine\ORM\Internal\Hydration\IterableResult');
        $iterableResultFromDb->expects($this->exactly(2))
            ->method('next')
            ->will($this->onConsecutiveCalls([$importProperty], false));

        $repositoryMock = $this->getImportPropertyRepositoryMock();
        $repositoryMock->expects($this->once())
            ->method('getNotProcessedImportProperties')
            ->with($this->equalTo($import), $this->equalTo(null))
            ->will($this->returnValue($iterableResultFromDb));

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->at(0))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:ImportProperty'))
            ->will($this->returnValue($repositoryMock));

        $repositoryMock = $this->getBaseMock(UnitMappingRepository::class);
        $repositoryMock->expects($this->once())
            ->method('getMappingForImport')
            ->with($this->equalTo($group), $this->equalTo('test_ext__unit_id'))
            ->will($this->returnValue(null));

        $emMock->expects($this->at(1))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:UnitMapping'))
            ->will($this->returnValue($repositoryMock));
        $emMock->expects($this->exactly(2))
            ->method('flush');

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|PropertyManager $propertyManagerMock
         */
        $propertyManagerMock = $this->getMock(
            '\RentJeeves\CoreBundle\Services\PropertyManager',
            ['getOrCreatePropertyByAddressFields'],
            [],
            '',
            false
        );
        // need for test SingleProperty
        $this->writeAttribute($propertyManagerMock, 'logger', $this->getLoggerMock());
        $this->writeAttribute($propertyManagerMock, 'em', $this->getEntityManagerMock());
        $propertyManagerMock->expects($this->once())
            ->method('getOrCreatePropertyByAddressFields')
            ->willReturn($property);

        $validatorMock = $this->getValidatorMock();
        $validatorMock->method('validate')->willReturn([]);

        $loader = new UnmappedLoader(
            $emMock,
            $propertyManagerMock,
            $validatorMock,
            $this->getLoggerMock()
        );
        $loader->loadData($import);

        $this->assertEquals(
            ImportPropertyStatus::NEW_PROPERTY_AND_UNIT,
            $importProperty->getStatus(),
            'Status is not updated'
        );
        $this->assertTrue($property->isSingle(), 'Property should be single');
        $this->assertTrue(
            $property->getPropertyGroups()->contains($group),
            'Property doesn`t have relation with current group'
        );

        $this->assertTrue($importProperty->isProcessed(), 'Status of process is not updated');
    }

    /**
     * @test
     */
    public function shouldSetErrorStatusForImportPropertyIfHasNoUnitMappingId()
    {
        $holding = new Holding();
        $this->writeIdAttribute($holding, 1);

        $group = new Group();
        $this->writeIdAttribute($group, 1);
        $group->setHolding($holding);

        $import = new Import();
        $this->writeIdAttribute($import, 1);
        $import->setGroup($group);

        $importProperty = new ImportProperty();
        $importProperty->setImport($import);
        $importProperty->setAllowMultipleProperties(false);
        $importProperty->setAddressHasUnits(true);
        $importProperty->setUnitName('testUnitName');

        $propertyAddress = new PropertyAddress();
        $property = new Property();
        $property->setPropertyAddress($propertyAddress);

        $iterableResultFromDb = $this->getBaseMock('\Doctrine\ORM\Internal\Hydration\IterableResult');
        $iterableResultFromDb->expects($this->exactly(2))
            ->method('next')
            ->will($this->onConsecutiveCalls([$importProperty], false));

        $repositoryMock = $this->getImportPropertyRepositoryMock();
        $repositoryMock->expects($this->once())
            ->method('getNotProcessedImportProperties')
            ->with($this->equalTo($import), $this->equalTo(null))
            ->will($this->returnValue($iterableResultFromDb));

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->at(0))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:ImportProperty'))
            ->will($this->returnValue($repositoryMock));

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|PropertyManager $propertyManagerMock
         */
        $propertyManagerMock = $this->getMock(
            '\RentJeeves\CoreBundle\Services\PropertyManager',
            ['getOrCreatePropertyByAddressFields'],
            [],
            '',
            false
        );
        $propertyManagerMock->expects($this->once())
            ->method('getOrCreatePropertyByAddressFields')
            ->willReturn($property);

        $validator = $this->getValidatorMock();

        $loader = new UnmappedLoader(
            $emMock,
            $propertyManagerMock,
            $validator,
            $this->getLoggerMock()
        );
        $loader->loadData($import);

        $this->assertEquals(ImportPropertyStatus::ERROR, $importProperty->getStatus(), 'Status is not updated');
        $this->assertContains(
            'ExternalUnitId is required field',
            current($importProperty->getErrorMessages()),
            'Message is not updated'
        );
        $this->assertTrue($importProperty->isProcessed(), 'Status of process is not updated');
    }

    /**
     * @test
     */
    public function shouldCreateNewMultiPropertyAndSetCorrectStatusForImportProperty()
    {
        $holding = new Holding();
        $this->writeIdAttribute($holding, 1);

        $group = new Group();
        $this->writeIdAttribute($group, 1);
        $group->setHolding($holding);

        $import = new Import();
        $this->writeIdAttribute($import, 1);
        $import->setGroup($group);

        $importProperty = new ImportProperty();
        $importProperty->setImport($import);
        $importProperty->setAllowMultipleProperties(false);
        $importProperty->setAddressHasUnits(true);
        $importProperty->setUnitName('testUnitName');
        $importProperty->setExternalUnitId('testExtUnitId');

        $propertyAddress = new PropertyAddress();
        $property = new Property();
        $property->setPropertyAddress($propertyAddress);

        $iterableResultFromDb = $this->getBaseMock('\Doctrine\ORM\Internal\Hydration\IterableResult');
        $iterableResultFromDb->expects($this->exactly(2))
            ->method('next')
            ->will($this->onConsecutiveCalls([$importProperty], false));

        $repositoryMock = $this->getImportPropertyRepositoryMock();
        $repositoryMock->expects($this->once())
            ->method('getNotProcessedImportProperties')
            ->with($this->equalTo($import), $this->equalTo(null))
            ->will($this->returnValue($iterableResultFromDb));

        $unitMappingRepositoryMock = $this->getUnitMappingRepositoryMock();
        $unitMappingRepositoryMock->expects($this->once())
            ->method('getMappingForImport')
            ->will($this->returnValue(null));

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->at(0))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:ImportProperty'))
            ->will($this->returnValue($repositoryMock));
        $emMock->expects($this->at(1))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:UnitMapping'))
            ->will($this->returnValue($unitMappingRepositoryMock));
        $emMock->expects($this->exactly(2))
            ->method('flush');
        $emMock->expects($this->exactly(3))
        ->method('persist')
            ->withConsecutive(
                $this->isInstanceOf('\RentJeeves\DataBundle\Entity\Unit'),
                $this->isInstanceOf('\RentJeeves\DataBundle\Entity\UnitMapping'),
                $this->isInstanceOf('\RentJeeves\DataBundle\Entity\Property')
            );

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|PropertyManager $propertyManagerMock
         */
        $propertyManagerMock = $this->getMock(
            '\RentJeeves\CoreBundle\Services\PropertyManager',
            ['getOrCreatePropertyByAddressFields'],
            [],
            '',
            false
        );
        $propertyManagerMock->expects($this->once())
            ->method('getOrCreatePropertyByAddressFields')
            ->willReturn($property);

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        $loader = new UnmappedLoader(
            $emMock,
            $propertyManagerMock,
            $validator,
            $this->getLoggerMock()
        );
        $loader->loadData($import);

        $this->assertEquals(
            ImportPropertyStatus::NEW_PROPERTY_AND_UNIT,
            $importProperty->getStatus(),
            'Status is not updated'
        );
        $this->assertFalse($property->isSingle(), 'Property should be single');
        $this->assertTrue(
            $property->getPropertyGroups()->contains($group),
            'Property doesn`t have relation with current group'
        );

        $this->assertTrue($importProperty->isProcessed(), 'Status of process is not updated');
    }

    /**
     * @test
     */
    public function shouldCreateNewUnitForExistsPropertyAndSetCorrectStatusFroImportProperty()
    {
        $holding = new Holding();
        $this->writeIdAttribute($holding, 1);

        $group = new Group();
        $this->writeIdAttribute($group, 1);
        $group->setHolding($holding);

        $import = new Import();
        $this->writeIdAttribute($import, 1);
        $import->setGroup($group);

        $importProperty = new ImportProperty();
        $importProperty->setImport($import);
        $importProperty->setAllowMultipleProperties(false);
        $importProperty->setAddressHasUnits(true);
        $importProperty->setUnitName('testUnitName');
        $importProperty->setExternalUnitId('testExtUnitId');

        $propertyAddress = new PropertyAddress();
        $property = new Property();
        $this->writeIdAttribute($property, 1);
        $property->setPropertyAddress($propertyAddress);

        $iterableResultFromDb = $this->getBaseMock('\Doctrine\ORM\Internal\Hydration\IterableResult');
        $iterableResultFromDb->expects($this->exactly(2))
            ->method('next')
            ->will($this->onConsecutiveCalls([$importProperty], false));

        $repositoryMock = $this->getImportPropertyRepositoryMock();
        $repositoryMock->expects($this->once())
            ->method('getNotProcessedImportProperties')
            ->with($this->equalTo($import), $this->equalTo(null))
            ->will($this->returnValue($iterableResultFromDb));

        $unitMappingRepositoryMock = $this->getUnitMappingRepositoryMock();
        $unitMappingRepositoryMock->expects($this->once())
            ->method('getMappingForImport')
            ->will($this->returnValue(null));

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->at(0))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:ImportProperty'))
            ->will($this->returnValue($repositoryMock));
        $emMock->expects($this->at(1))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:UnitMapping'))
            ->will($this->returnValue($unitMappingRepositoryMock));
        $emMock->expects($this->exactly(2))
            ->method('flush');
        $emMock->expects($this->exactly(3))
        ->method('persist')
            ->withConsecutive(
                $this->isInstanceOf('\RentJeeves\DataBundle\Entity\Unit'),
                $this->isInstanceOf('\RentJeeves\DataBundle\Entity\UnitMapping'),
                $this->isInstanceOf('\RentJeeves\DataBundle\Entity\Property')
            );

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|PropertyManager $propertyManagerMock
         */
        $propertyManagerMock = $this->getMock(
            '\RentJeeves\CoreBundle\Services\PropertyManager',
            ['getOrCreatePropertyByAddressFields'],
            [],
            '',
            false
        );
        $propertyManagerMock->expects($this->once())
            ->method('getOrCreatePropertyByAddressFields')
            ->willReturn($property);

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        $loader = new UnmappedLoader(
            $emMock,
            $propertyManagerMock,
            $validator,
            $this->getLoggerMock()
        );
        $loader->loadData($import);

        $this->assertEquals(
            ImportPropertyStatus::NEW_UNIT,
            $importProperty->getStatus(),
            'Status is not updated'
        );
        $this->assertTrue(
            $property->getPropertyGroups()->contains($group),
            'Property doesn`t have relation with current group'
        );

        $this->assertTrue($importProperty->isProcessed(), 'Status of process is not updated');
    }

    /**
     * @test
     */
    public function shouldJustSetCorrectStatusForImportPropertyIfUnitIsFound()
    {
        $holding = new Holding();
        $this->writeIdAttribute($holding, 1);

        $group = new Group();
        $this->writeIdAttribute($group, 1);
        $group->setHolding($holding);

        $import = new Import();
        $this->writeIdAttribute($import, 1);
        $import->setGroup($group);

        $importProperty = new ImportProperty();
        $importProperty->setImport($import);
        $importProperty->setAllowMultipleProperties(false);
        $importProperty->setAddressHasUnits(true);
        $importProperty->setUnitName('testUnitName');
        $importProperty->setExternalUnitId('testExternalUnitId');

        $propertyAddress = new PropertyAddress();
        $property = new Property();
        $this->writeIdAttribute($property, 1);
        $property->setPropertyAddress($propertyAddress);

        $unit = new Unit();
        $this->writeIdAttribute($unit, 1);
        $unit->setProperty($property);
        $unitMapping = new UnitMapping();
        $unitMapping->setUnit($unit);
        $unit->setUnitMapping($unitMapping);

        $iterableResultFromDb = $this->getBaseMock('\Doctrine\ORM\Internal\Hydration\IterableResult');
        $iterableResultFromDb->expects($this->exactly(2))
            ->method('next')
            ->will($this->onConsecutiveCalls([$importProperty], false));

        $repositoryMock = $this->getImportPropertyRepositoryMock();
        $repositoryMock->expects($this->once())
            ->method('getNotProcessedImportProperties')
            ->with($this->equalTo($import), $this->equalTo(null))
            ->will($this->returnValue($iterableResultFromDb));

        $unitMappingRepositoryMock = $this->getUnitMappingRepositoryMock();
        $unitMappingRepositoryMock->expects($this->once())
            ->method('getMappingForImport')
            ->will($this->returnValue($unitMapping));

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->at(0))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:ImportProperty'))
            ->will($this->returnValue($repositoryMock));
        $emMock->expects($this->at(1))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:UnitMapping'))
            ->will($this->returnValue($unitMappingRepositoryMock));
        $emMock->expects($this->exactly(2))
            ->method('flush');
        $emMock->expects($this->exactly(3))
            ->method('persist')
            ->withConsecutive(
                $this->isInstanceOf('\RentJeeves\DataBundle\Entity\Unit'),
                $this->isInstanceOf('\RentJeeves\DataBundle\Entity\UnitMapping'),
                $this->isInstanceOf('\RentJeeves\DataBundle\Entity\Property')
            );

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|PropertyManager $propertyManagerMock
         */
        $propertyManagerMock = $this->getMock(
            '\RentJeeves\CoreBundle\Services\PropertyManager',
            ['getOrCreatePropertyByAddressFields'],
            [],
            '',
            false
        );
        $propertyManagerMock->expects($this->once())
            ->method('getOrCreatePropertyByAddressFields')
            ->willReturn($property);

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        $loader = new UnmappedLoader(
            $emMock,
            $propertyManagerMock,
            $validator,
            $this->getLoggerMock()
        );
        $loader->loadData($import);

        $this->assertEquals(
            ImportPropertyStatus::MATCH,
            $importProperty->getStatus(),
            'Status is not updated'
        );
        $this->assertTrue(
            $property->getPropertyGroups()->contains($group),
            'Property doesn`t have relation with current group'
        );

        $this->assertTrue($importProperty->isProcessed(), 'Status of process is not updated');
    }

    /**
     * @test
     */
    public function shouldSetErrorStatusForImportPropertyIfUnitFromUnitMappingRelatedWithAnotherProperty()
    {
        $holding = new Holding();
        $this->writeIdAttribute($holding, 1);

        $group = new Group();
        $this->writeIdAttribute($group, 1);
        $group->setHolding($holding);

        $import = new Import();
        $this->writeIdAttribute($import, 1);
        $import->setGroup($group);

        $importProperty = new ImportProperty();
        $importProperty->setImport($import);
        $importProperty->setAllowMultipleProperties(false);
        $importProperty->setAddressHasUnits(true);
        $importProperty->setUnitName('testUnitName');
        $importProperty->setExternalUnitId('testExternalUnitId');

        $propertyAddress = new PropertyAddress();
        $property = new Property();
        $this->writeIdAttribute($property, 1);
        $property->setPropertyAddress($propertyAddress);

        $unit = new Unit();
        $this->writeIdAttribute($unit, 1);
        $unit->setProperty(new Property());
        $unitMapping = new UnitMapping();
        $unitMapping->setUnit($unit);
        $unit->setUnitMapping($unitMapping);

        $iterableResultFromDb = $this->getBaseMock('\Doctrine\ORM\Internal\Hydration\IterableResult');
        $iterableResultFromDb->expects($this->exactly(2))
            ->method('next')
            ->will($this->onConsecutiveCalls([$importProperty], false));

        $repositoryMock = $this->getImportPropertyRepositoryMock();
        $repositoryMock->expects($this->once())
            ->method('getNotProcessedImportProperties')
            ->with($this->equalTo($import), $this->equalTo(null))
            ->will($this->returnValue($iterableResultFromDb));

        $unitMappingRepositoryMock = $this->getUnitMappingRepositoryMock();
        $unitMappingRepositoryMock->expects($this->once())
            ->method('getMappingForImport')
            ->will($this->returnValue($unitMapping));

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->at(0))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:ImportProperty'))
            ->will($this->returnValue($repositoryMock));
        $emMock->expects($this->at(1))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:UnitMapping'))
            ->will($this->returnValue($unitMappingRepositoryMock));
        $emMock->expects($this->once())
            ->method('flush')
            ->with($this->equalTo($importProperty));

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|PropertyManager $propertyManagerMock
         */
        $propertyManagerMock = $this->getMock(
            '\RentJeeves\CoreBundle\Services\PropertyManager',
            ['getOrCreatePropertyByAddressFields'],
            [],
            '',
            false
        );
        $propertyManagerMock->expects($this->once())
            ->method('getOrCreatePropertyByAddressFields')
            ->willReturn($property);

        $validator = $this->getValidatorMock();
        $validator->expects($this->never())
            ->method('validate')
            ->willReturn([]);

        $loader = new UnmappedLoader(
            $emMock,
            $propertyManagerMock,
            $validator,
            $this->getLoggerMock()
        );
        $loader->loadData($import);

        $this->assertEquals(
            ImportPropertyStatus::ERROR,
            $importProperty->getStatus(),
            'Status is not updated'
        );
        $this->assertContains(
            'Unit#1 found by external unit id and group but do not belong to processing property',
            current($importProperty->getErrorMessages()),
            'Message is not updated'
        );
        $this->assertTrue($importProperty->isProcessed(), 'Status of process is not updated');
    }

    /**
     * @test
     */
    public function shouldSetErrorStatusForImportPropertyIfNewUnitIsInvalid()
    {
        $holding = new Holding();
        $this->writeIdAttribute($holding, 1);

        $group = new Group();
        $this->writeIdAttribute($group, 1);
        $group->setHolding($holding);

        $import = new Import();
        $this->writeIdAttribute($import, 1);
        $import->setGroup($group);

        $importProperty = new ImportProperty();
        $importProperty->setImport($import);
        $importProperty->setAllowMultipleProperties(false);
        $importProperty->setAddressHasUnits(true);
        $importProperty->setUnitName('testUnitName');
        $importProperty->setExternalUnitId('testExternalUnitId');

        $propertyAddress = new PropertyAddress();
        $property = new Property();
        $this->writeIdAttribute($property, 1);
        $property->setPropertyAddress($propertyAddress);

        $unit = new Unit();
        $this->writeIdAttribute($unit, 1);
        $unit->setProperty($property);
        $unitMapping = new UnitMapping();
        $unitMapping->setUnit($unit);
        $unit->setUnitMapping($unitMapping);

        $iterableResultFromDb = $this->getBaseMock('\Doctrine\ORM\Internal\Hydration\IterableResult');
        $iterableResultFromDb->expects($this->exactly(2))
            ->method('next')
            ->will($this->onConsecutiveCalls([$importProperty], false));

        $repositoryMock = $this->getImportPropertyRepositoryMock();
        $repositoryMock->expects($this->once())
            ->method('getNotProcessedImportProperties')
            ->with($this->equalTo($import), $this->equalTo(null))
            ->will($this->returnValue($iterableResultFromDb));

        $unitMappingRepositoryMock = $this->getUnitMappingRepositoryMock();
        $unitMappingRepositoryMock->expects($this->once())
            ->method('getMappingForImport')
            ->will($this->returnValue($unitMapping));

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->at(0))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:ImportProperty'))
            ->will($this->returnValue($repositoryMock));
        $emMock->expects($this->at(1))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:UnitMapping'))
            ->will($this->returnValue($unitMappingRepositoryMock));
        $emMock->expects($this->once())
            ->method('flush')
            ->with($this->equalTo($importProperty));

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|PropertyManager $propertyManagerMock
         */
        $propertyManagerMock = $this->getMock(
            '\RentJeeves\CoreBundle\Services\PropertyManager',
            ['getOrCreatePropertyByAddressFields'],
            [],
            '',
            false
        );
        $propertyManagerMock->expects($this->once())
            ->method('getOrCreatePropertyByAddressFields')
            ->willReturn($property);

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList(
                [new ConstraintViolation('test', 'test', [], 'test', 'test', 'test')]
            ));

        $loader = new UnmappedLoader(
            $emMock,
            $propertyManagerMock,
            $validator,
            $this->getLoggerMock()
        );
        $loader->loadData($import);

        $this->assertEquals(
            ImportPropertyStatus::ERROR,
            $importProperty->getStatus(),
            'Status is not updated'
        );
        $this->assertContains(
            'Unit is not valid: test : test',
            current($importProperty->getErrorMessages()),
            'Message is not updated'
        );

        $this->assertTrue($importProperty->isProcessed(), 'Status of process is not updated');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PropertyManager
     */
    protected function getPropertyManagerMock()
    {
        return $this->getBaseMock('\RentJeeves\CoreBundle\Services\PropertyManager');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ImportPropertyRepository
     */
    protected function getImportPropertyRepositoryMock()
    {
        return $this->getBaseMock('\RentJeeves\DataBundle\Entity\ImportPropertyRepository');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|UnitMappingRepository
     */
    protected function getUnitMappingRepositoryMock()
    {
        return $this->getBaseMock('\RentJeeves\DataBundle\Entity\UnitMappingRepository');
    }
}
