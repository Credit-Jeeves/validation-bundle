<?php

namespace RentJeeves\LandlordBundle\Tests\Unit\Import\Storage;

use RentJeeves\DataBundle\Entity\ImportMappingByProperty;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\ExternalApiBundle\Tests\Services\MRI\MRIClientCase;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class StorageMRICase extends BaseTestCase
{
    use WriteAttributeExtensionTrait;

    /**
     * @test
     */
    public function shouldNotGetMappingFromDB()
    {
        $this->load(true);
        /** @var Landlord $landlord */
        $landlord = $this->getEntityManager()->getRepository('RjDataBundle:Landlord')
            ->findOneByEmail('landlord1@example.com');
        $this->assertNotEmpty($landlord);

        $storageMriMock = $this->getMock(
            'RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageMRI',
            [],
            [],
            '',
            false
        );
        $landlordSessionMock = $this->getMock('RentJeeves\CoreBundle\Session\Landlord', [], [], '', false);
        $landlordSessionMock->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($landlord));

        $this->writeAttribute($storageMriMock, 'em', $this->getEntityManager());
        $this->writeAttribute($storageMriMock, 'sessionLandlordManager', $landlordSessionMock);

        $storageMriMock->expects($this->any())
            ->method('getImportExternalPropertyId')
            ->will($this->returnValue(MRIClientCase::PROPERTY_ID));

        $storageMriMockReflection = new \ReflectionClass($storageMriMock);
        $getMappingFromDBMethod = $storageMriMockReflection->getMethod('getMappingFromDB');
        $getMappingFromDBMethod->setAccessible(true);

        $this->assertFalse($getMappingFromDBMethod->invoke($storageMriMock));

        $this->writeAttribute($storageMriMock, 'sessionLandlordManager', $landlordSessionMock);
        $property = $this->getEntityManager()->getRepository('RjDataBundle:Property')->findOneBy(
            [
                'street' => 'Broadway',
                'number' => '770',
                'zip'    => '10003'
            ]
        );
        $propertyMapping = $property->getPropertyMappingByHolding($landlord->getHolding());
        $propertyMapping->setExternalPropertyId(uniqid());
        $importMappingByPropertyChoice = new ImportMappingByProperty();
        $importMappingByPropertyChoice->setMappingData([1, 2]);
        $importMappingByPropertyChoice->setProperty($property);
        $this->getEntityManager()->persist($importMappingByPropertyChoice);
        $this->getEntityManager()->flush();

        $this->assertFalse($getMappingFromDBMethod->invoke($storageMriMock));

        $importMappingByPropertyChoice->setMappingData([]);
        $this->getEntityManager()->flush();

        $this->assertFalse($getMappingFromDBMethod->invoke($storageMriMock));
    }

    /**
     * @test
     */
    public function shouldGetMappingFromDB()
    {
        $this->load(true);
        $property = $this->getEntityManager()->getRepository('RjDataBundle:Property')->findOneBy(
            [
                'street' => 'Broadway',
                'number' => '770',
                'zip'    => '10003'
            ]
        );

        $this->assertNotEmpty($property);
        $landlord = $this->getEntityManager()->getRepository('RjDataBundle:Landlord')
            ->findOneByEmail('landlord1@example.com');
        $this->assertNotEmpty($landlord);
        $propertyMapping = $property->getPropertyMappingByHolding($landlord->getHolding());
        $this->assertNotEmpty($propertyMapping);
        $propertyMapping->setExternalPropertyId(MRIClientCase::PROPERTY_ID);
        $importMappingByPropertyChoice = new ImportMappingByProperty();
        $importMappingByPropertyChoice->setMappingData([1, 2]);
        $importMappingByPropertyChoice->setProperty($property);
        $this->getEntityManager()->persist($importMappingByPropertyChoice);
        $this->getEntityManager()->flush();

        $storageMriMock = $this->getMockBuilder('RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageMRI')
            ->disableOriginalConstructor()
            ->getMock();

        $this->writeAttribute($storageMriMock, 'em', $this->getEntityManager());
        $landlordSessionMock = $this->getMock('RentJeeves\CoreBundle\Session\Landlord', [], [], '', false);
        $landlordSessionMock->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($landlord));
        $this->writeAttribute($storageMriMock, 'sessionLandlordManager', $landlordSessionMock);

        $storageMriMock->expects($this->any())
            ->method('getImportExternalPropertyId')
            ->will($this->returnValue(MRIClientCase::PROPERTY_ID));

        $storageMriMockReflection = new \ReflectionClass($storageMriMock);
        $getMappingFromDBMethod = $storageMriMockReflection->getMethod('getMappingFromDB');
        $getMappingFromDBMethod->setAccessible(true);
        $this->assertNotEmpty($getMappingFromDBMethod->invoke($storageMriMock));
    }
}
