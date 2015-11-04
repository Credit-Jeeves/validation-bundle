<?php

namespace RentJeeves\LandlordBundle\Tests\Unit\Accounting\ImportLandlord\Storage;

use RentJeeves\DataBundle\Entity\ImportApiMapping;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class StorageCase extends UnitTestBase
{
    use WriteAttributeExtensionTrait;

    /**
     * @test
     */
    public function shouldReturnMappingFromDBForWhenWeHaveSuchMappingInDB()
    {
        /** @var Landlord $landlord */
        $externalApiStorageMock = $this->getMock(
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

        $this->writeAttribute($externalApiStorageMock, 'em', $em);
        $this->writeAttribute($externalApiStorageMock, 'sessionLandlordManager', $landlordSessionMock);

        $externalApiStorageMock->expects($this->any())
            ->method('getImportExternalPropertyId')
            ->will($this->returnValue('1234'));

        $externalApiStorageMockReflection = new \ReflectionClass($externalApiStorageMock);
        $getMappingFromDBMethod = $externalApiStorageMockReflection->getMethod('getMappingFromDB');
        $getMappingFromDBMethod->setAccessible(true);
        /** We should not get mapping from */
        $this->assertFalse($getMappingFromDBMethod->invoke($externalApiStorageMock), 'We got result, but should not');
    }

    /**
     * @test
     */
    public function shouldNotReturnMappingFromDBForWhenWeDontHaveIt()
    {
        /** @var Landlord $landlord */
        $externalApiStorageMock = $this->getMock(
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

        $this->writeAttribute($externalApiStorageMock, 'em', $em);
        $this->writeAttribute($externalApiStorageMock, 'sessionLandlordManager', $landlordSessionMock);

        $externalApiStorageMock->expects($this->any())
            ->method('getImportExternalPropertyId')
            ->will($this->returnValue('1234'));

        $externalApiStorageMockReflection = new \ReflectionClass($externalApiStorageMock);
        $getMappingFromDBMethod = $externalApiStorageMockReflection->getMethod('getMappingFromDB');
        $getMappingFromDBMethod->setAccessible(true);
        /** We should not get mapping from */
        $this->assertArrayHasKey('hi', $getMappingFromDBMethod->invoke($externalApiStorageMock), 'We don\'t have mapping');
    }

    /**
     * @test
     */
    public function shouldOverrideDataWhenWeHaveForItDefaultValue()
    {
        $externalApiStorageMock = $this->getMock(
            'RentJeeves\LandlordBundle\Accounting\Import\Storage\ExternalApiStorage',
            [],
            [],
            '',
            false,
            true
        );

        $mapping = new ImportApiMapping();
        $mapping->setCity('Default City');
        $mapping->setStreet('Default Street');
        $mapping->setState('Default State');
        $mapping->setZip('Default Zip');
        $mapping->setMappingData(
            [
                1 => Mapping::KEY_CITY,
                2 => 'Not used',
                3 => Mapping::KEY_MOVE_IN,
                4 => Mapping::KEY_LEASE_END,
                5 => Mapping::KEY_ZIP,
                6 => Mapping::FIRST_NAME_TENANT,
                7 => Mapping::LAST_NAME_TENANT,
                8 => Mapping::KEY_EMAIL,
                9 => Mapping::KEY_MOVE_OUT,
                10 => Mapping::KEY_RESIDENT_ID,
                11 => Mapping::KEY_STREET,
                12 => Mapping::KEY_RENT,
                13 => Mapping::KEY_STATE
            ]
        );

        $landlordSessionMock = $this->getMock('RentJeeves\CoreBundle\Session\Landlord', [], [], '', false);
        $landlordSessionMock->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue(new Landlord()));

        $entityRepository= $this->getMock('Doctrine\ORM\EntityRepository', [], [], '', false);
        $entityRepository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($mapping));

        $em = $this->getMock('Doctrine\ORM\EntityManager', [], [], '', false);
        $em->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($entityRepository));

        $this->writeAttribute($externalApiStorageMock, 'em', $em);
        $this->writeAttribute($externalApiStorageMock, 'sessionLandlordManager', $landlordSessionMock);

        $externalApiStorageMockReflection = new \ReflectionClass($externalApiStorageMock);
        $overrideValuesByImportApiMapping = $externalApiStorageMockReflection->getMethod(
            'overrideValuesByImportApiMapping'
        );
        $overrideValuesByImportApiMapping->setAccessible(true);

        $data = [
            '5555',
            'residentId',
            'unit',
            'move In',
            'lease end',
            'z123p',
            'tenant name',
            'tenant name',
            'email',
            'move out',
            '52555',
            'rent',
            ''
        ];

        $result = $overrideValuesByImportApiMapping->invoke($externalApiStorageMock, $data);
        $revertResult = array_flip($result);
        $this->assertArrayHasKey($mapping->getCity(), $revertResult, 'City not rewriting');
        $this->assertArrayHasKey($mapping->getStreet(), $revertResult, 'Street not rewriting');
        $this->assertArrayHasKey($mapping->getZip(), $revertResult, 'Zip not rewriting');
        $this->assertArrayHasKey($mapping->getState(), $revertResult, 'State not rewriting');
    }
}
