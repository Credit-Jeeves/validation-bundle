<?php
namespace RentJeeves\CoreBundle\Tests\Unit\Services;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CoreBundle\Exception\ContractMovementManagerException;
use RentJeeves\CoreBundle\Services\ContractMovementManager;
use RentJeeves\CoreBundle\Services\UnitDeduplicator;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class UnitDeduplicatorCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;
    use WriteAttributeExtensionTrait;

    /**
     * @test
     */
    public function shouldCreateNewObjectUnitDeduplicator()
    {
        new UnitDeduplicator(
            $this->getContractMovementMock(),
            $this->getEntityManagerMock(),
            $this->getLoggerMock()
        );
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Exception\UnitDeduplicatorException
     * @expectedExceptionMessage ERROR: the dstProperty#1 is not in the same group as the srcUnit#1
     */
    public function shouldLogErrorAndThrowExceptionIfUnitAndPropertyHaveDifferentGroups()
    {
        $unit = new Unit();
        $this->writeIdAttribute($unit, 1);
        $unit->setGroup(new Group());

        $property = new Property();
        $this->writeIdAttribute($property, 1);
        $property->addPropertyGroup(new Group());

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('ERROR: the dstProperty#1 is not in the same group as the srcUnit#1'));

        $deduplicator = new UnitDeduplicator(
            $this->getContractMovementMock(),
            $this->getEntityManagerMock(),
            $logger
        );
        $deduplicator->deduplicate($unit, $property);

    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Exception\UnitDeduplicatorException
     * @expectedExceptionMessage the externalUnitID=test2 of the dstUnit#2 is different than the externalUnitID=test1
     */
    public function shouldLogErrorAndThrowExceptionIfSrcUnitAndDstUnitHaveDifferentExternalUnitId()
    {
        $srcUnit = new Unit();
        $srcUnit->setName('test');
        $this->writeIdAttribute($srcUnit, 1);
        $srcUnit->setGroup($group = new Group());

        $srcUnitMapping = new UnitMapping();
        $srcUnitMapping->setExternalUnitId('test1');

        $srcUnit->setUnitMapping($srcUnitMapping);

        $firstUnit = new Unit();
        $this->writeIdAttribute($firstUnit, 2);
        $firstUnitMapping = new UnitMapping();
        $firstUnitMapping->setExternalUnitId('test2');
        $firstUnit->setUnitMapping($firstUnitMapping);

        $property = new Property();
        $property->addPropertyGroup($group);

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains(
                'the externalUnitID=test2 of the dstUnit#2 is different than the externalUnitID=test1'
            ));

        $unitRepositoryMock = $this->getUnitRepositoryMock();
        $unitRepositoryMock->expects($this->once())
            ->method($this->equalTo('findFirstUnitsWithSameNameByUnitAndPropertyAndSortById'))
            ->will($this->returnValue($firstUnit));

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method($this->equalTo('getRepository'))
            ->with($this->equalTo('RjDataBundle:Unit'))
            ->will($this->returnValue($unitRepositoryMock));

        $deduplicator = new UnitDeduplicator(
            $this->getContractMovementMock(),
            $em,
            $logger
        );

        $deduplicator->deduplicate($srcUnit, $property);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Exception\UnitDeduplicatorException
     * @expectedExceptionMessage ERROR: there are multiple external unit ID="test1"
     */
    public function shouldLogErrorAndThrowExceptionIfGroupHaveMultipleExternalUnit()
    {
        $srcUnit = new Unit();
        $srcUnit->setName('test');
        $this->writeIdAttribute($srcUnit, 1);
        $srcUnit->setGroup($group = new Group());

        $srcUnitMapping = new UnitMapping();
        $srcUnitMapping->setExternalUnitId('test1');

        $srcUnit->setUnitMapping($srcUnitMapping);

        $firstUnit = new Unit();
        $this->writeIdAttribute($firstUnit, 2);
        $firstUnitMapping = new UnitMapping();
        $firstUnitMapping->setExternalUnitId('test1');
        $firstUnit->setUnitMapping($firstUnitMapping);

        $property = new Property();
        $property->addPropertyGroup($group);

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains(
                'ERROR: there are multiple external unit ID="test1"'
            ));

        $unitRepositoryMock = $this->getUnitRepositoryMock();
        $unitRepositoryMock->expects($this->once())
            ->method('findFirstUnitsWithSameNameByUnitAndPropertyAndSortById')
            ->will($this->returnValue($firstUnit));
        $unitRepositoryMock->expects($this->once())
            ->method('findOtherUnitsWithSameExternalUnitIdInGroupExcludeUnit')
            ->with($this->equalTo($srcUnit), $this->equalTo($firstUnit))
            ->will($this->returnValue([new Unit()]));

        $em = $this->getEntityManagerMock();
        $em->expects($this->any())
            ->method($this->equalTo('getRepository'))
            ->with($this->equalTo('RjDataBundle:Unit'))
            ->will($this->returnValue($unitRepositoryMock));

        $deduplicator = new UnitDeduplicator(
            $this->getContractMovementMock(),
            $em,
            $logger
        );

        $deduplicator->deduplicate($srcUnit, $property);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Exception\UnitDeduplicatorException
     * @expectedExceptionMessage Can`t update Unit#2 for Contract#1 : test
     */
    public function shouldLogErrorAndThrowExceptionIfCantMoveContract()
    {
        $srcUnit = new Unit();
        $srcUnit->setName('test');
        $this->writeIdAttribute($srcUnit, 1);
        $srcUnit->setGroup($group = new Group());

        $firstUnit = new Unit();
        $this->writeIdAttribute($firstUnit, 2);

        $property = new Property();
        $property->addPropertyGroup($group);

        $contract = new Contract();
        $this->writeIdAttribute($contract, 1);

        $srcUnit->addContract($contract);

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains(
                'Can`t update Unit#2 for Contract#1 : test'
            ));

        $unitRepositoryMock = $this->getUnitRepositoryMock();
        $unitRepositoryMock->expects($this->once())
            ->method('findFirstUnitsWithSameNameByUnitAndPropertyAndSortById')
            ->will($this->returnValue($firstUnit));

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method($this->equalTo('getRepository'))
            ->with($this->equalTo('RjDataBundle:Unit'))
            ->will($this->returnValue($unitRepositoryMock));

        $contractMovementMock = $this->getContractMovementMock();
        $contractMovementMock->expects($this->once())
            ->method($this->equalTo('move'))
            ->with($this->equalTo($contract), $this->equalTo($firstUnit))
            ->willThrowException(new ContractMovementManagerException('test'));

        $deduplicator = new UnitDeduplicator(
            $contractMovementMock,
            $em,
            $logger
        );

        $deduplicator->deduplicate($srcUnit, $property);
    }

    /**
     * @test
     */
    public function shouldRemoveSourceUnitIfInputDataIsValid()
    {
        $srcUnit = new Unit();
        $srcUnit->setName('test');
        $this->writeIdAttribute($srcUnit, 1);
        $srcUnit->setGroup($group = new Group());

        $firstUnit = new Unit();
        $this->writeIdAttribute($firstUnit, 2);

        $property = new Property();
        $property->addPropertyGroup($group);

        $logger = $this->getLoggerMock();
        $logger->expects($this->at(0))
            ->method('info')
            ->with($this->stringContains(
                'Unit#1 is deleted.'
            ));
        $logger->expects($this->at(1))
            ->method('info')
            ->with($this->stringContains(
                'Migration Units and Contracts from one Property to another is finished.'
            ));

        $unitRepositoryMock = $this->getUnitRepositoryMock();
        $unitRepositoryMock->expects($this->once())
            ->method('findFirstUnitsWithSameNameByUnitAndPropertyAndSortById')
            ->will($this->returnValue($firstUnit));

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method($this->equalTo('getRepository'))
            ->with($this->equalTo('RjDataBundle:Unit'))
            ->will($this->returnValue($unitRepositoryMock));
        $em->expects($this->once())
            ->method($this->equalTo('remove'))
            ->with($this->equalTo($srcUnit));

        $deduplicator = new UnitDeduplicator(
            $this->getContractMovementMock(),
            $em,
            $logger
        );

        $deduplicator->deduplicate($srcUnit, $property);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ContractMovementManager
     */
    protected function getContractMovementMock()
    {
        return $this->getMock(
            'RentJeeves\CoreBundle\Services\ContractMovementManager',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\DataBundle\Entity\UnitRepository
     */
    protected function getUnitRepositoryMock()
    {
        return $this->getMock(
            'RentJeeves\DataBundle\Entity\UnitRepository',
            [],
            [],
            '',
            false
        );
    }
}
