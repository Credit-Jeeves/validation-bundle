<?php

namespace RentJeeves\LandlordBundle\Tests\Unit\Import\Storage;

use RentJeeves\DataBundle\Entity\ImportApiMapping;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\ExternalApiBundle\Tests\Services\AMSI\AMSIClientCase;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class StorageAMSICase extends BaseTestCase
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

        $storageResmanMock = $this->getMock(
            'RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageAMSI',
            [],
            [],
            '',
            false
        );
        $landlordSessionMock = $this->getMock('RentJeeves\CoreBundle\Session\Landlord', [], [], '', false);
        $landlordSessionMock->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($landlord));

        $this->writeAttribute($storageResmanMock, 'em', $this->getEntityManager());
        $this->writeAttribute($storageResmanMock, 'sessionLandlordManager', $landlordSessionMock);

        $storageResmanMock->expects($this->any())
            ->method('getImportExternalPropertyId')
            ->will($this->returnValue(AMSIClientCase::EXTERNAL_PROPERTY_ID));

        $storageMriMockReflection = new \ReflectionClass($storageResmanMock);
        $getMappingFromDBMethod = $storageMriMockReflection->getMethod('getMappingFromDB');
        $getMappingFromDBMethod->setAccessible(true);

        $this->assertFalse($getMappingFromDBMethod->invoke($storageResmanMock));

        $this->writeAttribute($storageResmanMock, 'sessionLandlordManager', $landlordSessionMock);

        $importApiMapping = new ImportApiMapping();
        $importApiMapping->setMappingData([1, 2]);
        $importApiMapping->setExternalPropertyId(uniqid());
        $importApiMapping->setHolding($landlord->getHolding());
        $this->getEntityManager()->persist($importApiMapping);
        $this->getEntityManager()->flush();

        $this->assertFalse($getMappingFromDBMethod->invoke($storageResmanMock));

        $importApiMapping->setMappingData([]);
        $this->getEntityManager()->flush();

        $this->assertFalse($getMappingFromDBMethod->invoke($storageResmanMock));
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
        $propertyMapping->setExternalPropertyId(AMSIClientCase::EXTERNAL_PROPERTY_ID);
        $importApiMapping = new ImportApiMapping();
        $importApiMapping->setMappingData([1, 2]);
        $importApiMapping->setExternalPropertyId(AMSIClientCase::EXTERNAL_PROPERTY_ID);
        $importApiMapping->setHolding($landlord->getHolding());
        $this->getEntityManager()->persist($importApiMapping);
        $this->getEntityManager()->flush();

        $storageResmanMock = $this->getMockBuilder('RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageAMSI')
            ->disableOriginalConstructor()
            ->getMock();

        $this->writeAttribute($storageResmanMock, 'em', $this->getEntityManager());
        $landlordSessionMock = $this->getMock('RentJeeves\CoreBundle\Session\Landlord', [], [], '', false);
        $landlordSessionMock->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($landlord));
        $this->writeAttribute($storageResmanMock, 'sessionLandlordManager', $landlordSessionMock);

        $storageResmanMock->expects($this->any())
            ->method('getImportExternalPropertyId')
            ->will($this->returnValue(AMSIClientCase::EXTERNAL_PROPERTY_ID));

        $storageMriMockReflection = new \ReflectionClass($storageResmanMock);
        $getMappingFromDBMethod = $storageMriMockReflection->getMethod('getMappingFromDB');
        $getMappingFromDBMethod->setAccessible(true);
        $this->assertNotEmpty($getMappingFromDBMethod->invoke($storageResmanMock));
    }
}
