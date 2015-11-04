<?php

namespace RentJeeves\LandlordBundle\Tests\Unit\Accounting\ImportLandlord\Storage;

use RentJeeves\DataBundle\Entity\ImportApiMapping;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class StorageCase extends UnitTestBase
{
    use WriteAttributeExtensionTrait;

    /**
     * @test
     */
    public function shouldReturnMappingFromDBForExternalApi()
    {
        /** @var Landlord $landlord */
        $externalApiStorage = $this->getMock(
            'RentJeeves\LandlordBundle\Accounting\Import\Storage\ExternalApiStorage',
            [],
            [],
            '',
            false
        );
        $landlordSessionMock = $this->getMock('RentJeeves\CoreBundle\Session\Landlord', [], [], '', false);
        $landlordSessionMock->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue(new Landlord()));

        $entityRepository= $this->getMock('Doctrine\ORM\EntityRepository', [], [], '', false);
        $entityRepository->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(false));

        $em = $this->getMock('Doctrine\ORM\EntityManager', [], [], '', false);
        $em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($entityRepository));

        $this->writeAttribute($externalApiStorage, 'em', $em);
        $this->writeAttribute($externalApiStorage, 'sessionLandlordManager', $landlordSessionMock);

        $externalApiStorage->expects($this->any())
            ->method('getImportExternalPropertyId')
            ->will($this->returnValue('1234'));

        $storageMriMockReflection = new \ReflectionClass($externalApiStorage);
        $getMappingFromDBMethod = $storageMriMockReflection->getMethod('getMappingFromDB');
        $getMappingFromDBMethod->setAccessible(true);
        /** We should not get mapping from */
        $this->assertFalse($getMappingFromDBMethod->invoke($externalApiStorage), 'We got result, but should not');
    }

    /**
     * @test
     */
    public function shouldNotReturnMappingFromDBForExternalApi()
    {
        /** @var Landlord $landlord */
        $externalApiStorage = $this->getMock(
            'RentJeeves\LandlordBundle\Accounting\Import\Storage\ExternalApiStorage',
            [],
            [],
            '',
            false
        );
        $landlordSessionMock = $this->getMock('RentJeeves\CoreBundle\Session\Landlord', [], [], '', false);
        $landlordSessionMock->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue(new Landlord()));

        $mapping = new ImportApiMapping();
        $mapping->setMappingData(['hi' => 'hello']);

        $entityRepository= $this->getMock('Doctrine\ORM\EntityRepository', [], [], '', false);
        $entityRepository->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($mapping));

        $em = $this->getMock('Doctrine\ORM\EntityManager', [], [], '', false);
        $em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($entityRepository));

        $this->writeAttribute($externalApiStorage, 'em', $em);
        $this->writeAttribute($externalApiStorage, 'sessionLandlordManager', $landlordSessionMock);

        $externalApiStorage->expects($this->any())
            ->method('getImportExternalPropertyId')
            ->will($this->returnValue('1234'));

        $storageMriMockReflection = new \ReflectionClass($externalApiStorage);
        $getMappingFromDBMethod = $storageMriMockReflection->getMethod('getMappingFromDB');
        $getMappingFromDBMethod->setAccessible(true);
        /** We should not get mapping from */
        $this->assertArrayHasKey('hi', $getMappingFromDBMethod->invoke($externalApiStorage), 'We don\'t have mapping');
    }
}
