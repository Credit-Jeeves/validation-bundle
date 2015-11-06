<?php

namespace RentJeeves\LandlordBundle\Tests\Unit\Accounting\ImportLandlord\Storage;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\ImportApiMapping;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageMRI;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\ExternalApiBundle\Model\MRI\Value;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;

class StorageCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function shouldCheckSaveToFileMri()
    {
        $customer = new Value();
        $customer->setIsCurrent(StorageMRI::IS_CURRENT);
        $customer->setLeaseStart('2010-09-08 12:09:08');
        $customer->setBuildingAddress('BuildingAddress');
        $customer->setLeaseEnd('2025-09-08 12:09:08');
        $customer->setLeaseMonthToMonth('y');
        $customer->setPropertyId('12');
        $customer->setBuildingId('33');
        $customer->setUnitId('22');
        $customer->setLeaseMonthlyRentAmount(900);
        $customer->setLeaseBalance(123);

        $customer->setState('State');
        $customer->setCity('City');
        $customer->setAddress('Address');
        $customer->setZipCode('09287');

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
                13 => Mapping::KEY_STATE,
                14 => 'Not Used',
                15 => 'Not Used',
                16 => 'Not Used',
                17 => 'Not Used',
                18 => 'Not Used',
                19 => 'Not Used',
                20 => 'Not Used',
                21 => 'Not Used',
            ]
        );

        $entityRepository = $this->getEntityRepositoryMock();
        $entityRepository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($mapping));
        $em = $this->getEntityManagerMock();
        $em->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($entityRepository));
        $landlord = new Landlord();
        $landlord->setHolding(new Holding());
        $sessionLandlordManager = $this->getSessionLandlordMock();
        $sessionLandlordManager->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($landlord));
        $session = $this->getSessionMock();

        /** @var StorageMRI $storageMRI */
        $storageMRI = new StorageMRI(
                $session,
                $this->getLoggerMock(),
                $em,
                $sessionLandlordManager
        );
        $fileName = uniqid() . '.csv';
        $realPath = $storageMRI->getFileDirectory().$fileName;

        $session->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(
                    function ($subject) use ($fileName) {
                        if ($subject === 'importExternalPropertyId') {
                            return 'propertyId';
                        }
                        if ($subject === 'importFileName') {
                            return $fileName;
                        }
                    }
                )
            );

        $this->assertTrue($storageMRI->saveToFile([$customer]), 'We should success write to file.');
        $data = file_get_contents($realPath);
        $this->assertRegexp(sprintf('/%s/', $mapping->getState()), $data);
        $this->assertRegexp(sprintf('/%s/', $mapping->getCity()), $data);
        $this->assertRegexp(sprintf('/%s/', $mapping->getStreet()), $data);
        $this->assertRegexp(sprintf('/%s/', $mapping->getZip()), $data);
    }
}
