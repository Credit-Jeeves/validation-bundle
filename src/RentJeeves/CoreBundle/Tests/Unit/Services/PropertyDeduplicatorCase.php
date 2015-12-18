<?php
namespace RentJeeves\CoreBundle\Tests\Unit\Services;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CoreBundle\Exception\UnitDeduplicatorException;
use RentJeeves\CoreBundle\Services\PropertyDeduplicator;
use RentJeeves\CoreBundle\Services\UnitDeduplicator;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class PropertyDeduplicatorCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;
    use WriteAttributeExtensionTrait;

    /**
     * @test
     */
    public function shouldCreateNewObjectPropertyDeduplicator()
    {
        new PropertyDeduplicator(
            $this->getUnitDeduplicatorMock(),
            $this->getEntityManagerMock(),
            $this->getLoggerMock()
        );
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Exception\PropertyDeduplicatorException
     * @expectedExceptionMessage Not found any Properties for PropertyAddress#777
     */
    public function shouldLogErrorAndThrowExceptionIfInputAddressDoesNotHaveAnyProperty()
    {
        $propertyAddress = new PropertyAddress();
        $this->writeIdAttribute($propertyAddress, 777);

        $propertyRepositoryMock = $this->getPropertyRepositoryMock();
        $propertyRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(
                $this->equalTo(['propertyAddress' => $propertyAddress]),
                $this->equalTo(['id' => 'asc'])
            )
            ->will($this->returnValue(null));

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:Property'))
            ->will($this->returnValue($propertyRepositoryMock));

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo('Not found any Properties for PropertyAddress#777'));

        $propertyDeduplicator = new PropertyDeduplicator(
            $this->getUnitDeduplicatorMock(),
            $em,
            $logger
        );

        $propertyDeduplicator->deduplicate($propertyAddress);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Exception\PropertyDeduplicatorException
     * @expectedExceptionMessage Not found any dubbed Properties for Property#1 and PropertyAddress#777
     */
    public function shouldLogErrorAndThrowExceptionIfInputAddressDoesNotHaveDubbedProperties()
    {
        $propertyAddress = new PropertyAddress();
        $this->writeIdAttribute($propertyAddress, 777);

        $property = new Property();
        $this->writeIdAttribute($property, 1);

        $propertyRepositoryMock = $this->getPropertyRepositoryMock();
        $propertyRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(
                $this->equalTo(['propertyAddress' => $propertyAddress]),
                $this->equalTo(['id' => 'asc'])
            )
            ->will($this->returnValue($property));
        $propertyRepositoryMock->expects($this->once())
            ->method('findAllOtherPropertiesWithSamePropertyAddress')
            ->with($this->equalTo($property))
            ->will($this->returnValue([]));

        $em = $this->getEntityManagerMock();
        $em->expects($this->exactly(2))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:Property'))
            ->will($this->returnValue($propertyRepositoryMock));

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo('Not found any dubbed Properties for Property#1 and PropertyAddress#777'));

        $propertyDeduplicator = new PropertyDeduplicator(
            $this->getUnitDeduplicatorMock(),
            $em,
            $logger
        );

        $propertyDeduplicator->deduplicate($propertyAddress);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Exception\PropertyDeduplicatorException
     * @expectedExceptionMessage Property#1 is used by several different holdings cannot deduplicate multi-holding
     */
    public function shouldLogErrorAndThrowExceptionIfDstPropertyHaveSeveralPropertyMappings()
    {
        $propertyAddress = new PropertyAddress();
        $this->writeIdAttribute($propertyAddress, 777);

        $dstProperty = new Property();
        $this->writeIdAttribute($dstProperty, 1);
        $dstProperty->addPropertyMapping(new PropertyMapping());
        $dstProperty->addPropertyMapping(new PropertyMapping());

        $srcProperty = new Property();
        $this->writeIdAttribute($srcProperty, 2);

        $propertyRepositoryMock = $this->getPropertyRepositoryMock();
        $propertyRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(
                $this->equalTo(['propertyAddress' => $propertyAddress]),
                $this->equalTo(['id' => 'asc'])
            )
            ->will($this->returnValue($dstProperty));
        $propertyRepositoryMock->expects($this->once())
            ->method('findAllOtherPropertiesWithSamePropertyAddress')
            ->with($this->equalTo($dstProperty))
            ->will($this->returnValue([$srcProperty]));

        $em = $this->getEntityManagerMock();
        $em->expects($this->exactly(2))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:Property'))
            ->will($this->returnValue($propertyRepositoryMock));

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains(
                    'ERROR: Property#1 is used by several different holdings cannot deduplicate multi-holding'
                )
            );

        $propertyDeduplicator = new PropertyDeduplicator(
            $this->getUnitDeduplicatorMock(),
            $em,
            $logger
        );

        $propertyDeduplicator->deduplicate($propertyAddress);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Exception\PropertyDeduplicatorException
     * @expectedExceptionMessage Property#3 is used by several different holdings cannot deduplicate multi-holding
     */
    public function shouldLogErrorAndThrowExceptionIfSecondSrcPropertyHaveSeveralPropertyMappings()
    {
        $propertyAddress = new PropertyAddress();
        $this->writeIdAttribute($propertyAddress, 777);

        $dstProperty = new Property();
        $this->writeIdAttribute($dstProperty, 1);
        $dstProperty->addPropertyMapping(new PropertyMapping());

        $srcProperty1 = new Property();
        $this->writeIdAttribute($srcProperty1, 2);
        $srcProperty1->addPropertyMapping(new PropertyMapping());

        $srcProperty2 = new Property();
        $this->writeIdAttribute($srcProperty2, 3);
        $srcProperty2->addPropertyMapping(new PropertyMapping());
        $srcProperty2->addPropertyMapping(new PropertyMapping());

        $propertyRepositoryMock = $this->getPropertyRepositoryMock();
        $propertyRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(
                $this->equalTo(['propertyAddress' => $propertyAddress]),
                $this->equalTo(['id' => 'asc'])
            )
            ->will($this->returnValue($dstProperty));
        $propertyRepositoryMock->expects($this->once())
            ->method('findAllOtherPropertiesWithSamePropertyAddress')
            ->with($this->equalTo($dstProperty))
            ->will($this->returnValue([$srcProperty1, $srcProperty2]));

        $em = $this->getEntityManagerMock();
        $em->expects($this->exactly(2))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:Property'))
            ->will($this->returnValue($propertyRepositoryMock));

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains(
                    'ERROR: Property#3 is used by several different holdings cannot deduplicate multi-holding'
                )
            );

        $propertyDeduplicator = new PropertyDeduplicator(
            $this->getUnitDeduplicatorMock(),
            $em,
            $logger
        );

        $propertyDeduplicator->deduplicate($propertyAddress);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Exception\PropertyDeduplicatorException
     * @expectedExceptionMessage externalPropertyId="test1" of the dstProperty#1 is different than the srcProperty#2
     */
    public function shouldLogErrorAndThrowExceptionIfDstAndSrcPropertiesHaveDifferentPropertyMappings()
    {
        $propertyAddress = new PropertyAddress();
        $this->writeIdAttribute($propertyAddress, 777);

        $dstProperty = new Property();
        $this->writeIdAttribute($dstProperty, 1);
        $dstPropertyMapping = new PropertyMapping();
        $dstPropertyMapping->setExternalPropertyId('test1');
        $dstProperty->addPropertyMapping($dstPropertyMapping);

        $srcProperty = new Property();
        $this->writeIdAttribute($srcProperty, 2);
        $srcPropertyMapping = new PropertyMapping();
        $srcPropertyMapping->setExternalPropertyId('test2');
        $srcProperty->addPropertyMapping($srcPropertyMapping);

        $propertyRepositoryMock = $this->getPropertyRepositoryMock();
        $propertyRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(
                $this->equalTo(['propertyAddress' => $propertyAddress]),
                $this->equalTo(['id' => 'asc'])
            )
            ->will($this->returnValue($dstProperty));
        $propertyRepositoryMock->expects($this->once())
            ->method('findAllOtherPropertiesWithSamePropertyAddress')
            ->with($this->equalTo($dstProperty))
            ->will($this->returnValue([$srcProperty]));

        $em = $this->getEntityManagerMock();
        $em->expects($this->exactly(2))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:Property'))
            ->will($this->returnValue($propertyRepositoryMock));

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains(
                    'externalPropertyId="test1" of the dstProperty#1 is different than the srcProperty#2'
                )
            );

        $propertyDeduplicator = new PropertyDeduplicator(
            $this->getUnitDeduplicatorMock(),
            $em,
            $logger
        );

        $propertyDeduplicator->deduplicate($propertyAddress);
    }

    /**
     * @test
     */
    public function shouldLogErrorAndUpdateSecondSrcPropertyIfCantDedupeUnitsForFirstSrcProperty()
    {
        $propertyAddress = new PropertyAddress();
        $propertyAddress->setIsSingle(false);
        $this->writeIdAttribute($propertyAddress, 777);

        $group1 = new Group();
        $this->writeIdAttribute($group1, 1);
        $group2 = new Group();
        $this->writeIdAttribute($group2, 2);

        $dstProperty = new Property();
        $this->writeIdAttribute($dstProperty, 1);
        $dstPropertyMapping = new PropertyMapping();
        $dstPropertyMapping->setExternalPropertyId('test1');
        $dstProperty->addPropertyMapping($dstPropertyMapping);
        $dstProperty->addPropertyGroup($group1);
        $dstProperty->addPropertyGroup($group2);

        $srcProperty1 = new Property();
        $this->writeIdAttribute($srcProperty1, 2);
        $srcPropertyMapping = new PropertyMapping();
        $srcPropertyMapping->setExternalPropertyId('test1');
        $srcProperty1->addPropertyMapping($srcPropertyMapping);
        $srcProperty1->addPropertyGroup($group2);
        $srcProperty1->addPropertyGroup($group1);
        $srcProperty1->addUnit($srcUnit1 = new Unit());
        $srcProperty1->setPropertyAddress($propertyAddress);

        $srcProperty2 = new Property();
        $this->writeIdAttribute($srcProperty2, 3);
        $srcProperty2->addPropertyMapping($srcPropertyMapping);
        $srcProperty2->addPropertyGroup($group1);
        $srcProperty2->addPropertyGroup($group2);
        $srcProperty2->addUnit($srcUnit2 = new Unit());
        $srcProperty2->setPropertyAddress($propertyAddress);

        $propertyRepositoryMock = $this->getPropertyRepositoryMock();
        $propertyRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(
                $this->equalTo(['propertyAddress' => $propertyAddress]),
                $this->equalTo(['id' => 'asc'])
            )
            ->will($this->returnValue($dstProperty));
        $propertyRepositoryMock->expects($this->once())
            ->method('findAllOtherPropertiesWithSamePropertyAddress')
            ->with($this->equalTo($dstProperty))
            ->will($this->returnValue([$srcProperty1, $srcProperty2]));

        $propertyRepositoryMock->expects($this->at(2))
            ->method('find')
            ->will($this->returnValue($srcProperty1));
        $propertyRepositoryMock->expects($this->at(3))
            ->method('find')
            ->will($this->returnValue($dstProperty));
        $propertyRepositoryMock->expects($this->at(4))
            ->method('find')
            ->will($this->returnValue($srcProperty2));
        $propertyRepositoryMock->expects($this->at(5))
            ->method('find')
            ->will($this->returnValue($dstProperty));

        $em = $this->getEntityManagerMock();
        $em->expects($this->exactly(6))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:Property'))
            ->will($this->returnValue($propertyRepositoryMock));
        $em->expects($this->exactly(2))
            ->method('clear');
        $em->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($srcProperty2));
        $em->expects($this->once())
            ->method('flush');

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('ERROR: Can`t dedupe Property#2 : test'));
        $logger->expects($this->once())
            ->method('info')
            ->with($this->stringContains('Property#3 is deduplicated.'));

        $unitDeduplicator = $this->getUnitDeduplicatorMock();
        $unitDeduplicator->expects($this->at(1))
            ->method('deduplicate')
            ->with($this->equalTo($srcUnit1), $this->equalTo($dstProperty))
            ->willThrowException(new UnitDeduplicatorException('test'));
        $unitDeduplicator->expects($this->at(2))
            ->method('deduplicate')
            ->with($this->equalTo($srcUnit2), $this->equalTo($dstProperty));

        $propertyDeduplicator = new PropertyDeduplicator(
            $unitDeduplicator,
            $em,
            $logger
        );

        $propertyDeduplicator->deduplicate($propertyAddress);
    }

    /**
     * @test
     */
    public function shouldDeduplicateSrcPropertyAndRemoveUnitIfDubbedPropertyIsSingle()
    {
        $propertyAddress = new PropertyAddress();
        $propertyAddress->setIsSingle(true);
        $this->writeIdAttribute($propertyAddress, 777);

        $group1 = new Group();
        $this->writeIdAttribute($group1, 1);
        $group2 = new Group();
        $this->writeIdAttribute($group2, 2);

        $dstProperty = new Property();
        $this->writeIdAttribute($dstProperty, 1);
        $dstPropertyMapping = new PropertyMapping();
        $dstPropertyMapping->setExternalPropertyId('test1');
        $dstProperty->addPropertyMapping($dstPropertyMapping);
        $dstProperty->addPropertyGroup($group1);
        $dstProperty->addPropertyGroup($group2);

        $srcProperty1 = new Property();
        $this->writeIdAttribute($srcProperty1, 2);
        $srcPropertyMapping = new PropertyMapping();
        $srcPropertyMapping->setExternalPropertyId('test1');
        $srcProperty1->addPropertyMapping($srcPropertyMapping);
        $srcProperty1->addPropertyGroup($group2);
        $srcProperty1->addPropertyGroup($group1);
        $srcProperty1->addUnit($srcUnit1 = new Unit());
        $srcProperty1->setPropertyAddress($propertyAddress);

        $propertyRepositoryMock = $this->getPropertyRepositoryMock();
        $propertyRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(
                $this->equalTo(['propertyAddress' => $propertyAddress]),
                $this->equalTo(['id' => 'asc'])
            )
            ->will($this->returnValue($dstProperty));
        $propertyRepositoryMock->expects($this->once())
            ->method('findAllOtherPropertiesWithSamePropertyAddress')
            ->with($this->equalTo($dstProperty))
            ->will($this->returnValue([$srcProperty1]));

        $propertyRepositoryMock->expects($this->at(2))
            ->method('find')
            ->will($this->returnValue($srcProperty1));
        $propertyRepositoryMock->expects($this->at(3))
            ->method('find')
            ->will($this->returnValue($dstProperty));

        $em = $this->getEntityManagerMock();
        $em->expects($this->exactly(4))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:Property'))
            ->will($this->returnValue($propertyRepositoryMock));
        $em->expects($this->exactly(1))
            ->method('clear');
        $em->expects($this->exactly(2)) // 1 for property + 1 for unit
            ->method('remove');
        $em->expects($this->once())
            ->method('flush');

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method('info')
            ->with($this->stringContains('Property#2 is deduplicated.'));

        $unitDeduplicator = $this->getUnitDeduplicatorMock();
        $unitDeduplicator->expects($this->at(1))
            ->method('deduplicate')
            ->with($this->equalTo($srcUnit1), $this->equalTo($dstProperty));

        $propertyDeduplicator = new PropertyDeduplicator(
            $unitDeduplicator,
            $em,
            $logger
        );

        $propertyDeduplicator->deduplicate($propertyAddress);
    }

    /**
     * @test
     */
    public function shouldOnlyLogMessagesWithoutFlushIfTurnOnDryRunMode()
    {
        $propertyAddress = new PropertyAddress();
        $propertyAddress->setIsSingle(false);
        $this->writeIdAttribute($propertyAddress, 777);

        $group1 = new Group();
        $this->writeIdAttribute($group1, 1);
        $group2 = new Group();
        $this->writeIdAttribute($group2, 2);

        $dstProperty = new Property();
        $this->writeIdAttribute($dstProperty, 1);
        $dstPropertyMapping = new PropertyMapping();
        $dstPropertyMapping->setExternalPropertyId('test1');
        $dstProperty->addPropertyMapping($dstPropertyMapping);
        $dstProperty->addPropertyGroup($group1);
        $dstProperty->addPropertyGroup($group2);

        $srcProperty1 = new Property();
        $this->writeIdAttribute($srcProperty1, 2);
        $srcPropertyMapping = new PropertyMapping();
        $srcPropertyMapping->setExternalPropertyId('test1');
        $srcProperty1->addPropertyMapping($srcPropertyMapping);
        $srcProperty1->addPropertyGroup($group2);
        $srcProperty1->addPropertyGroup($group1);
        $srcProperty1->addUnit($srcUnit1 = new Unit());
        $srcProperty1->setPropertyAddress($propertyAddress);

        $srcProperty2 = new Property();
        $this->writeIdAttribute($srcProperty2, 3);
        $srcProperty2->addPropertyMapping($srcPropertyMapping);
        $srcProperty2->addPropertyGroup($group1);
        $srcProperty2->addPropertyGroup($group2);
        $srcProperty2->addUnit($srcUnit2 = new Unit());
        $srcProperty2->setPropertyAddress($propertyAddress);

        $propertyRepositoryMock = $this->getPropertyRepositoryMock();
        $propertyRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(
                $this->equalTo(['propertyAddress' => $propertyAddress]),
                $this->equalTo(['id' => 'asc'])
            )
            ->will($this->returnValue($dstProperty));
        $propertyRepositoryMock->expects($this->once())
            ->method('findAllOtherPropertiesWithSamePropertyAddress')
            ->with($this->equalTo($dstProperty))
            ->will($this->returnValue([$srcProperty1, $srcProperty2]));

        $propertyRepositoryMock->expects($this->at(2))
            ->method('find')
            ->will($this->returnValue($srcProperty1));
        $propertyRepositoryMock->expects($this->at(3))
            ->method('find')
            ->will($this->returnValue($dstProperty));
        $propertyRepositoryMock->expects($this->at(4))
            ->method('find')
            ->will($this->returnValue($srcProperty2));
        $propertyRepositoryMock->expects($this->at(5))
            ->method('find')
            ->will($this->returnValue($dstProperty));

        $em = $this->getEntityManagerMock();
        $em->expects($this->exactly(6))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:Property'))
            ->will($this->returnValue($propertyRepositoryMock));
        $em->expects($this->exactly(2))
            ->method('clear');
        $em->expects($this->exactly(2))
            ->method('remove');
        $em->expects($this->never())
            ->method('flush');

        $logger = $this->getLoggerMock();
        $logger->expects($this->exactly(2))
            ->method('info');

        $unitDeduplicator = $this->getUnitDeduplicatorMock();
        $unitDeduplicator->expects($this->at(1))
            ->method('deduplicate')
            ->with($this->equalTo($srcUnit1), $this->equalTo($dstProperty));
        $unitDeduplicator->expects($this->at(2))
            ->method('deduplicate')
            ->with($this->equalTo($srcUnit2), $this->equalTo($dstProperty));

        $propertyDeduplicator = new PropertyDeduplicator(
            $unitDeduplicator,
            $em,
            $logger
        );
        $propertyDeduplicator->setDryRunMode(true);
        $propertyDeduplicator->deduplicate($propertyAddress);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|UnitDeduplicator
     */
    protected function getUnitDeduplicatorMock()
    {
        return $this->getMock(
            'RentJeeves\CoreBundle\Services\UnitDeduplicator',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\DataBundle\Entity\PropertyRepository
     */
    protected function getPropertyRepositoryMock()
    {
        return $this->getMock(
            'RentJeeves\DataBundle\Entity\PropertyRepository',
            [],
            [],
            '',
            false
        );
    }
}
