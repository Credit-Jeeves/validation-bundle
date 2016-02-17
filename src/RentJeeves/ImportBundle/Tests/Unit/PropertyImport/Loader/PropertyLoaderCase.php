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
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Entity\UnitMappingRepository;
use RentJeeves\DataBundle\Enum\ImportPropertyStatus;
use RentJeeves\ImportBundle\PropertyImport\Loader\PropertyLoader;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class PropertyLoaderCase extends UnitTestBase
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

        $extPropertyId = 'test';

        $importProperty = new ImportProperty();
        $importProperty->setImport($import);

        $iterableResultFromDb = $this->getBaseMock('\Doctrine\ORM\Internal\Hydration\IterableResult');
        $iterableResultFromDb->expects($this->exactly(2))
            ->method('next')
            ->will($this->onConsecutiveCalls([$importProperty], false));

        $repositoryMock = $this->getImportPropertyRepositoryMock();
        $repositoryMock->expects($this->once())
            ->method('getNotProcessedImportProperties')
            ->with($this->equalTo($import), $this->equalTo($extPropertyId))
            ->will($this->returnValue($iterableResultFromDb));

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:ImportProperty'))
            ->will($this->returnValue($repositoryMock));
        $emMock->expects($this->once())
            ->method('flush')
            ->with($this->equalTo($importProperty));

        $loader = new PropertyLoader(
            $emMock,
            $this->getPropertyManagerMock(),
            $this->getValidatorMock(),
            $this->getLoggerMock()
        );
        $loader->loadData($import, $extPropertyId);

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
    public function shouldCreateNewSinglePropertyAndSetCorrectStatusFroImportProperty()
    {
        $holding = new Holding();
        $this->writeIdAttribute($holding, 1);

        $group = new Group();
        $this->writeIdAttribute($group, 1);
        $group->setHolding($holding);

        $import = new Import();
        $this->writeIdAttribute($import, 1);
        $import->setGroup($group);

        $extPropertyId = 'test';

        $importProperty = new ImportProperty();
        $importProperty->setImport($import);
        $importProperty->setAllowMultipleProperties(true);
        $importProperty->setAddressHasUnits(false);
        $importProperty->setExternalPropertyId($extPropertyId);

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
            ->with($this->equalTo($import), $this->equalTo($extPropertyId))
            ->will($this->returnValue($iterableResultFromDb));

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:ImportProperty'))
            ->will($this->returnValue($repositoryMock));
        $emMock->expects($this->exactly(2))
            ->method('flush');

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|PropertyManager $propertyManagerMock
         */
        $propertyManagerMock = $this->getMock(
            '\RentJeeves\CoreBundle\Services\PropertyManager',
            ['getOrCreatePropertyByAddress'],
            [],
            '',
            false
        );
        // need for test SingleProperty
        $this->writeAttribute($propertyManagerMock, 'logger', $this->getLoggerMock());
        $this->writeAttribute($propertyManagerMock, 'em', $this->getEntityManagerMock());
        $propertyManagerMock->expects($this->once())
            ->method('getOrCreatePropertyByAddress')
            ->willReturn($property);

        $loader = new PropertyLoader(
            $emMock,
            $propertyManagerMock,
            $this->getValidatorMock(),
            $this->getLoggerMock()
        );
        $loader->loadData($import, $extPropertyId);

        $this->assertEquals(
            ImportPropertyStatus::NEW_PROPERTY_AND_UNIT,
            $importProperty->getStatus(),
            'Status is not updated'
        );
        $this->assertTrue($property->isSingle(), 'Property should be single');
        $this->assertEquals(
            $extPropertyId,
            $property->getPropertyMappingByHolding($holding)->getExternalPropertyId(),
            'Property should contain PropertyMapping for current Holding with correct extPropertyId'
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
    public function shouldCreateNewMultiPropertyAndSetCorrectStatusFroImportProperty()
    {
        $holding = new Holding();
        $this->writeIdAttribute($holding, 1);

        $group = new Group();
        $this->writeIdAttribute($group, 1);
        $group->setHolding($holding);

        $import = new Import();
        $this->writeIdAttribute($import, 1);
        $import->setGroup($group);

        $extPropertyId = 'test';

        $importProperty = new ImportProperty();
        $importProperty->setImport($import);
        $importProperty->setAllowMultipleProperties(true);
        $importProperty->setAddressHasUnits(true);
        $importProperty->setExternalPropertyId($extPropertyId);
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
            ->with($this->equalTo($import), $this->equalTo($extPropertyId))
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
        $emMock->expects($this->exactly(2)) // it checks creating Unit and UnitMapping
            ->method('persist')
            ->withConsecutive(
                    $this->isInstanceOf('\RentJeeves\DataBundle\Entity\Unit'),
                    $this->isInstanceOf('\RentJeeves\DataBundle\Entity\UnitMapping')
                );

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|PropertyManager $propertyManagerMock
         */
        $propertyManagerMock = $this->getMock(
            '\RentJeeves\CoreBundle\Services\PropertyManager',
            ['getOrCreatePropertyByAddress'],
            [],
            '',
            false
        );
        $propertyManagerMock->expects($this->once())
            ->method('getOrCreatePropertyByAddress')
            ->willReturn($property);

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        $loader = new PropertyLoader(
            $emMock,
            $propertyManagerMock,
            $validator,
            $this->getLoggerMock()
        );
        $loader->loadData($import, $extPropertyId);

        $this->assertEquals(
            ImportPropertyStatus::NEW_PROPERTY_AND_UNIT,
            $importProperty->getStatus(),
            'Status is not updated'
        );
        $this->assertFalse($property->isSingle(), 'Property should be single');
        $this->assertEquals(
            $extPropertyId,
            $property->getPropertyMappingByHolding($holding)->getExternalPropertyId(),
            'Property should contain PropertyMapping for current Holding with correct extPropertyId'
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

        $extPropertyId = 'test';

        $importProperty = new ImportProperty();
        $importProperty->setImport($import);
        $importProperty->setAllowMultipleProperties(true);
        $importProperty->setAddressHasUnits(true);
        $importProperty->setExternalPropertyId($extPropertyId);
        $importProperty->setUnitName('testUnitName');

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
            ->with($this->equalTo($import), $this->equalTo($extPropertyId))
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
        $emMock->expects($this->exactly(2)) // it checks creating Unit and UnitMapping
        ->method('persist')
            ->withConsecutive(
                $this->isInstanceOf('\RentJeeves\DataBundle\Entity\Unit'),
                $this->isInstanceOf('\RentJeeves\DataBundle\Entity\UnitMapping')
            );

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|PropertyManager $propertyManagerMock
         */
        $propertyManagerMock = $this->getMock(
            '\RentJeeves\CoreBundle\Services\PropertyManager',
            ['getOrCreatePropertyByAddress'],
            [],
            '',
            false
        );
        $propertyManagerMock->expects($this->once())
            ->method('getOrCreatePropertyByAddress')
            ->willReturn($property);

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        $loader = new PropertyLoader(
            $emMock,
            $propertyManagerMock,
            $validator,
            $this->getLoggerMock()
        );
        $loader->loadData($import, $extPropertyId);

        $this->assertEquals(
            ImportPropertyStatus::NEW_UNIT,
            $importProperty->getStatus(),
            'Status is not updated'
        );
        $this->assertEquals(
            $extPropertyId,
            $property->getPropertyMappingByHolding($holding)->getExternalPropertyId(),
            'Property should contain PropertyMapping for current Holding with correct extPropertyId'
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
    public function shouldJustSetCorrectStatusFroImportPropertyIfUnitIsFound()
    {
        $holding = new Holding();
        $this->writeIdAttribute($holding, 1);

        $group = new Group();
        $this->writeIdAttribute($group, 1);
        $group->setHolding($holding);

        $import = new Import();
        $this->writeIdAttribute($import, 1);
        $import->setGroup($group);

        $extPropertyId = 'test';

        $importProperty = new ImportProperty();
        $importProperty->setImport($import);
        $importProperty->setAllowMultipleProperties(true);
        $importProperty->setAddressHasUnits(true);
        $importProperty->setExternalPropertyId($extPropertyId);
        $importProperty->setUnitName('testUnitName');

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
            ->with($this->equalTo($import), $this->equalTo($extPropertyId))
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
        $emMock->expects($this->exactly(2)) // it checks creating Unit and UnitMapping
            ->method('persist')
            ->withConsecutive(
                $this->isInstanceOf('\RentJeeves\DataBundle\Entity\Unit'),
                $this->isInstanceOf('\RentJeeves\DataBundle\Entity\UnitMapping')
            );

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|PropertyManager $propertyManagerMock
         */
        $propertyManagerMock = $this->getMock(
            '\RentJeeves\CoreBundle\Services\PropertyManager',
            ['getOrCreatePropertyByAddress'],
            [],
            '',
            false
        );
        $propertyManagerMock->expects($this->once())
            ->method('getOrCreatePropertyByAddress')
            ->willReturn($property);

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        $loader = new PropertyLoader(
            $emMock,
            $propertyManagerMock,
            $validator,
            $this->getLoggerMock()
        );
        $loader->loadData($import, $extPropertyId);

        $this->assertEquals(
            ImportPropertyStatus::MATCH,
            $importProperty->getStatus(),
            'Status is not updated'
        );
        $this->assertEquals(
            $extPropertyId,
            $property->getPropertyMappingByHolding($holding)->getExternalPropertyId(),
            'Property should contain PropertyMapping for current Holding with correct extPropertyId'
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

        $extPropertyId = 'test';

        $importProperty = new ImportProperty();
        $importProperty->setImport($import);
        $importProperty->setAllowMultipleProperties(true);
        $importProperty->setAddressHasUnits(true);
        $importProperty->setExternalPropertyId($extPropertyId);
        $importProperty->setUnitName('testUnitName');

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
            ->with($this->equalTo($import), $this->equalTo($extPropertyId))
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
            ['getOrCreatePropertyByAddress'],
            [],
            '',
            false
        );
        $propertyManagerMock->expects($this->once())
            ->method('getOrCreatePropertyByAddress')
            ->willReturn($property);

        $validator = $this->getValidatorMock();
        $validator->expects($this->never())
            ->method('validate')
            ->willReturn([]);

        $loader = new PropertyLoader(
            $emMock,
            $propertyManagerMock,
            $validator,
            $this->getLoggerMock()
        );
        $loader->loadData($import, $extPropertyId);

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

        $extPropertyId = 'test';

        $importProperty = new ImportProperty();
        $importProperty->setImport($import);
        $importProperty->setAllowMultipleProperties(true);
        $importProperty->setAddressHasUnits(true);
        $importProperty->setExternalPropertyId($extPropertyId);
        $importProperty->setUnitName('testUnitName');

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
            ->with($this->equalTo($import), $this->equalTo($extPropertyId))
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
            ['getOrCreatePropertyByAddress'],
            [],
            '',
            false
        );
        $propertyManagerMock->expects($this->once())
            ->method('getOrCreatePropertyByAddress')
            ->willReturn($property);

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList(
                [new ConstraintViolation('test','test',[], 'test', 'test','test')]
            ));

        $loader = new PropertyLoader(
            $emMock,
            $propertyManagerMock,
            $validator,
            $this->getLoggerMock()
        );
        $loader->loadData($import, $extPropertyId);

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
     * @test
     */
    public function shouldSetErrorStatusForImportPropertyIfExternalPropertyIdsNotMatch()
    {
        $holding = new Holding();
        $this->writeIdAttribute($holding, 1);

        $group = new Group();
        $this->writeIdAttribute($group, 1);
        $group->setHolding($holding);

        $import = new Import();
        $this->writeIdAttribute($import, 1);
        $import->setGroup($group);

        $extPropertyId = 'test';

        $importProperty = new ImportProperty();
        $importProperty->setImport($import);
        $importProperty->setAllowMultipleProperties(false);
        $importProperty->setAddressHasUnits(true);
        $importProperty->setExternalPropertyId($extPropertyId);
        $importProperty->setUnitName('testUnitName');

        $propertyAddress = new PropertyAddress();
        $property = new Property();
        $this->writeIdAttribute($property, 1);
        $property->setPropertyAddress($propertyAddress);

        $propertyMapping = new PropertyMapping();
        $propertyMapping->setExternalPropertyId('test2');
        $propertyMapping->setHolding($holding);

        $property->addPropertyMapping($propertyMapping);

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
            ->with($this->equalTo($import), $this->equalTo($extPropertyId))
            ->will($this->returnValue($iterableResultFromDb));

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:ImportProperty'))
            ->will($this->returnValue($repositoryMock));
        $emMock->expects($this->once())
            ->method('flush')
            ->with($this->equalTo($importProperty));

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|PropertyManager $propertyManagerMock
         */
        $propertyManagerMock = $this->getMock(
            '\RentJeeves\CoreBundle\Services\PropertyManager',
            ['getOrCreatePropertyByAddress'],
            [],
            '',
            false
        );
        $propertyManagerMock->expects($this->once())
            ->method('getOrCreatePropertyByAddress')
            ->willReturn($property);

        $loader = new PropertyLoader(
            $emMock,
            $propertyManagerMock,
            $validator = $this->getValidatorMock(),
            $this->getLoggerMock()
        );
        $loader->loadData($import, $extPropertyId);

        $this->assertEquals(
            ImportPropertyStatus::ERROR,
            $importProperty->getStatus(),
            'Status is not updated'
        );
        $this->assertContains(
            'External property ids do not match for ImportProperty#0 (test2 !== test).',
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
