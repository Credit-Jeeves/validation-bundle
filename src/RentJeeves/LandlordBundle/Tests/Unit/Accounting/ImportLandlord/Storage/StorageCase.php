<?php

namespace RentJeeves\LandlordBundle\Tests\Unit\Accounting\ImportLandlord\Storage;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\ImportApiMapping;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\ExternalApiBundle\Model\AMSI\Occupant;
use RentJeeves\ExternalApiBundle\Model\ResMan\Address;
use RentJeeves\ExternalApiBundle\Model\ResMan\Customer;
use RentJeeves\ExternalApiBundle\Model\ResMan\Information;
use RentJeeves\ExternalApiBundle\Model\ResMan\Lease;
use RentJeeves\ExternalApiBundle\Model\ResMan\PropertyCustomer;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtUnit;
use RentJeeves\ExternalApiBundle\Model\ResMan\Unit;
use RentJeeves\ExternalApiBundle\Model\ResMan\UserName;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageAMSI;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageMRI;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageResman;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\ExternalApiBundle\Model\MRI\Value;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;
use RentJeeves\ExternalApiBundle\Model\ResMan\Customers;
use RentJeeves\ExternalApiBundle\Model\AMSI\Lease as AMSILease;
use RentJeeves\ExternalApiBundle\Model\AMSI\Unit as AMSIUnit;

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
        $realPath = $storageMRI->getFileDirectory() . $fileName;

        $session->expects($this->any())
            ->method('get')
            ->will($this->returnValue($fileName));

        $this->assertTrue($storageMRI->saveToFile([$customer]), 'We should success write to file.');
        $data = file_get_contents($realPath);
        $this->assertContains($mapping->getState(), $data, 'State not overrided');
        $this->assertContains($mapping->getCity(), $data, 'City not overrided');
        $this->assertContains($mapping->getStreet(), $data, 'Street no  overrided');
        $this->assertContains($mapping->getZip(), $data, 'Zip no overrided');
        unlink($realPath);
    }

    /**
     * @test
     */
    public function shouldCheckSaveToFileResman()
    {
        $rtCustomer = new RtCustomer();
        $customers = new Customers();
        $customer = new Customer();
        $customers->addCustomer($customer);
        $rtCustomer->setCustomers($customers);
        $rtCustomer->setCustomerId('1234');
        $rtCustomer->setPaymentAccepted('Yes');
        $rtCustomer->setRtUnit($rtUnit = new RtUnit());
        $rtUnit->setUnit($unit = new Unit());
        $unit->setInformation($info = new Information());
        $info->setBuildingID('11');
        $rtUnit->setUnitId(111);
        $customer->setType('current resident');
        $customer->setCustomerId('3333');
        $customer->setUserName($username = new UserName());
        $username->setFirstName('111');
        $username->setLastName(333);
        $customer->setAddress($address = new Address());
        $address->setCity('Hello');
        $address->setAddress1('Hi');
        $address->setPostalCode('HHH');
        $address->setState('US');
        $customer->setProperty($property = new PropertyCustomer());
        $property->setPrimaryId('1233');
        $customer->setLease($lease = new Lease());
        $lease->setLeaseFromDate(new \DateTime());
        $lease->setLeaseToDate(new \DateTime('+1 year'));
        $lease->setCurrentRent(1333);

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

        $storageResman = new StorageResman(
            $session,
            $this->getLoggerMock(),
            $em,
            $sessionLandlordManager
        );
        $fileName = uniqid() . '.csv';
        $realPath = $storageResman->getFileDirectory() . $fileName;

        $session->expects($this->any())
            ->method('get')
            ->will($this->returnValue($fileName));

        $this->assertTrue($storageResman->saveToFile([$rtCustomer]), 'We should success write to file.');
        $data = file_get_contents($realPath);
        $this->assertContains($mapping->getState(), $data, 'State not overrided');
        $this->assertContains($mapping->getCity(), $data, 'City not overrided');
        $this->assertContains($mapping->getStreet(), $data, 'Street no  overrided');
        $this->assertContains($mapping->getZip(), $data, 'Zip no overrided');
        unlink($realPath);
    }

    /**
     * @test
     */
    public function shouldCheckSaveToFileAMSI()
    {
        $lease = new AMSILease();
        $lease->setUnit($unit = new AMSIUnit());
        $unit->setAddress1('Address');
        $unit->setCity('HelloCity');
        $unit->setZip('aa');
        $unit->setState('state');
        $lease->setEndBalance(122);
        $lease->setRentAmount(500);
        $lease->setOccupants([$rommate = new Occupant()]);

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

        $storageResman = new StorageAMSI(
            $session,
            $this->getLoggerMock(),
            $em,
            $sessionLandlordManager
        );
        $fileName = uniqid() . '.csv';
        $realPath = $storageResman->getFileDirectory() . $fileName;

        $session->expects($this->any())
            ->method('get')
            ->will($this->returnValue($fileName));

        $this->assertTrue($storageResman->saveToFile([$lease]), 'We should success write to file.');
        $data = file_get_contents($realPath);
        $this->assertContains($mapping->getState(), $data, 'State not overrided');
        $this->assertContains($mapping->getCity(), $data, 'City not overrided');
        $this->assertContains($mapping->getStreet(), $data, 'Street no  overrided');
        $this->assertContains($mapping->getZip(), $data, 'Zip no overrided');
        unlink($realPath);
    }
}
