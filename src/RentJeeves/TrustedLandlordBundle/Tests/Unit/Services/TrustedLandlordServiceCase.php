<?php

namespace RentJeeves\TrustedLandlordBundle\Tests\Unit\Services;

use RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException;
use RentJeeves\CoreBundle\Services\AddressLookup\Model\Address;
use RentJeeves\CoreBundle\Services\AddressLookup\SmartyStreetsAddressLookupService;
use RentJeeves\DataBundle\Entity\CheckMailingAddress;
use RentJeeves\DataBundle\Entity\TrustedLandlord;
use RentJeeves\DataBundle\Enum\TrustedLandlordStatus;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TrustedLandlordBundle\Model\TrustedLandlordDTO;
use RentJeeves\TrustedLandlordBundle\Services\TrustedLandlordService;
use RentJeeves\TrustedLandlordBundle\Services\TrustedLandlordStatusManager;

class TrustedLandlordServiceCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**                         Lookup                           */

    /**
     * @test
     */
    public function shouldReturnNullIfAddressIsNotValid()
    {
        $lookupServiceMock = $this->getBaseMock(SmartyStreetsAddressLookupService::class);
        $lookupServiceMock->expects($this->once())
            ->method('lookup')
            ->will($this->throwException(new AddressLookupException()));

        $trustedLandlordService = new TrustedLandlordService(
            $this->getEntityManagerMock(),
            $this->getLoggerMock(),
            $lookupServiceMock,
            $this->getBaseMock(TrustedLandlordStatusManager::class)
        );

        $this->assertNull(
            $trustedLandlordService->lookup(new TrustedLandlordDTO()),
            'Service returned incorrect result.'
        );
    }

    /**
     * @test
     */
    public function shouldReturnNullIfSSIndexNotFoundInDb()
    {
        $address = new Address();
        $address->setLatitude(1);
        $address->setLongitude(1);
        $address->setNumber('test');

        $lookupServiceMock = $this->getBaseMock(SmartyStreetsAddressLookupService::class);
        $lookupServiceMock->expects($this->once())
            ->method('lookup')
            ->willReturn($address);

        $repository = $this->getEntityRepositoryMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['index' => 'test']))
            ->willReturn(null);

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:CheckMailingAddress'))
            ->willReturn($repository);

        $trustedLandlordService = new TrustedLandlordService(
            $em,
            $this->getLoggerMock(),
            $lookupServiceMock,
            $this->getBaseMock(TrustedLandlordStatusManager::class)
        );

        $this->assertNull(
            $trustedLandlordService->lookup(new TrustedLandlordDTO()),
            'Service returned incorrect result.'
        );
    }

    /**
     * @test
     */
    public function shouldReturnTrustedLandlordIfDbContainsAddress()
    {
        $address = new Address();
        $address->setLatitude(1);
        $address->setLongitude(1);
        $address->setNumber('test');

        $lookupServiceMock = $this->getBaseMock(SmartyStreetsAddressLookupService::class);
        $lookupServiceMock->expects($this->once())
            ->method('lookup')
            ->willReturn($address);

        $checkMailingAddress = new CheckMailingAddress();
        $checkMailingAddress->setTrustedLandlord($trustedLandlord = new TrustedLandlord());

        $repository = $this->getEntityRepositoryMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['index' => 'test']))
            ->willReturn($checkMailingAddress);

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:CheckMailingAddress'))
            ->willReturn($repository);

        $trustedLandlordService = new TrustedLandlordService(
            $em,
            $this->getLoggerMock(),
            $lookupServiceMock,
            $this->getBaseMock(TrustedLandlordStatusManager::class)
        );

        $this->assertInstanceOf(
            TrustedLandlord::class,
            $trustedLandlordService->lookup(new TrustedLandlordDTO()),
            'Service returned incorrect result.'
        );
    }
    /**                         Create                           */

    /**
     * @test
     * @expectedException \RentJeeves\TrustedLandlordBundle\Exception\TrustedLandlordServiceException
     */
    public function shouldThrowExceptionIfAddressIsNotValidWhenTryToCreate()
    {
        $lookupServiceMock = $this->getBaseMock(SmartyStreetsAddressLookupService::class);
        $lookupServiceMock->expects($this->once())
            ->method('lookup')
            ->will($this->throwException(new AddressLookupException()));

        $trustedLandlordService = new TrustedLandlordService(
            $this->getEntityManagerMock(),
            $this->getLoggerMock(),
            $lookupServiceMock,
            $this->getBaseMock(TrustedLandlordStatusManager::class)
        );

        $trustedLandlordService->create(new TrustedLandlordDTO());
    }

    /**
     * @test
     * @expectedException \RentJeeves\TrustedLandlordBundle\Exception\TrustedLandlordServiceException
     * @expectedExceptionMessage Cant create new TrustedLandlord
     */
    public function shouldThrowExceptionIfIfDbContainsAddress()
    {
        $address = new Address();
        $address->setLatitude(1);
        $address->setLongitude(1);
        $address->setNumber('test');

        $lookupServiceMock = $this->getBaseMock(SmartyStreetsAddressLookupService::class);
        $lookupServiceMock->expects($this->once())
            ->method('lookup')
            ->willReturn($address);

        $repository = $this->getEntityRepositoryMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['index' => 'test']))
            ->willReturn(new CheckMailingAddress());

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:CheckMailingAddress'))
            ->willReturn($repository);
        $em->expects($this->never())
            ->method('persist');
        $em->expects($this->never())
            ->method('flush');

        $trustedLandlordService = new TrustedLandlordService(
            $em,
            $this->getLoggerMock(),
            $lookupServiceMock,
            $this->getBaseMock(TrustedLandlordStatusManager::class)
        );

        $trustedLandlordService->create(new TrustedLandlordDTO());
    }

    /**
     * @test
     */
    public function shouldPersistAndFlushNewTrustedLandlordIfAddressIsValid()
    {
        $address = new Address();
        $address->setLatitude(1);
        $address->setLongitude(1);
        $address->setNumber('test');

        $lookupServiceMock = $this->getBaseMock(SmartyStreetsAddressLookupService::class);
        $lookupServiceMock->expects($this->once())
            ->method('lookup')
            ->willReturn($address);

        $repository = $this->getEntityRepositoryMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['index' => 'test']))
            ->willReturn(null);

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:CheckMailingAddress'))
            ->willReturn($repository);
        $em->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(TrustedLandlord::class));
        $em->expects($this->once())
            ->method('flush');

        $statusManager = $this->getBaseMock(TrustedLandlordStatusManager::class);
        // Pls uncomment for statusManager task
        $statusManager->expects($this->once())
            ->method('updateStatus')
            ->with($this->isInstanceOf(TrustedLandlord::class), $this->equalTo(TrustedLandlordStatus::NEWONE));

        $trustedLandlordService = new TrustedLandlordService(
            $em,
            $this->getLoggerMock(),
            $lookupServiceMock,
            $statusManager
        );

        $trustedLandlordService->create(new TrustedLandlordDTO());
    }

    /**                         updateStatus                           */

    /**
     * @test
     */
    public function shouldCallUpdateStatusViaStatusManagerAndUpdateTheEntity()
    {
        $trustedLandlord = new TrustedLandlord();
        $trustedLandlord->setCheckMailingAddress(new CheckMailingAddress());
        $trustedLandlordDTO = new TrustedLandlordDTO();
        $statusManager = $this->getBaseMock(TrustedLandlordStatusManager::class);
        $statusManager->expects($this->once())
            ->method('updateStatus')
            ->with(
                $this->equalTo($trustedLandlord),
                $this->equalTo(TrustedLandlordStatus::WAITING_FOR_INFO)
            );
        $address = new Address();
        $address->setLatitude(1);
        $address->setLongitude(1);
        $address->setNumber('test');

        $lookupServiceMock = $this->getBaseMock(SmartyStreetsAddressLookupService::class);
        $lookupServiceMock->expects($this->once())
            ->method('lookup')
            ->willReturn($address);
        $repository = $this->getEntityRepositoryMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['index' => 'test']))
            ->willReturn(null);
        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:CheckMailingAddress'))
            ->willReturn($repository);

        $trustedLandlordService = new TrustedLandlordService(
            $em,
            $this->getLoggerMock(),
            $lookupServiceMock,
            $statusManager
        );

        $trustedLandlordService->update($trustedLandlord, TrustedLandlordStatus::WAITING_FOR_INFO, $trustedLandlordDTO);
    }

    /**
     * @test
     */
    public function shouldJustCallUpdateStatusViaStatusManager()
    {
        $trustedLandlord = new TrustedLandlord();
        $trustedLandlord->setCheckMailingAddress(new CheckMailingAddress());
        $statusManager = $this->getBaseMock(TrustedLandlordStatusManager::class);
        $statusManager->expects($this->once())
            ->method('updateStatus')
            ->with(
                $this->equalTo($trustedLandlord),
                $this->equalTo(TrustedLandlordStatus::WAITING_FOR_INFO)
            );

        $trustedLandlordService = new TrustedLandlordService(
            $this->getEntityManagerMock(),
            $this->getLoggerMock(),
            $this->getBaseMock(SmartyStreetsAddressLookupService::class),
            $statusManager
        );

        $trustedLandlordService->update($trustedLandlord, TrustedLandlordStatus::WAITING_FOR_INFO);
    }
}
