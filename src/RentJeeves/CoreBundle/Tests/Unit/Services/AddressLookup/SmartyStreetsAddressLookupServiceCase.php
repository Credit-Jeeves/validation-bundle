<?php
namespace RentJeeves\CoreBundle\Tests\Unit\Services;

use RentJeeves\CoreBundle\Services\AddressLookup\Model\Address;
use RentJeeves\CoreBundle\Services\AddressLookup\SmartyStreetsAddressLookupService;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentTrack\SmartyStreetsBundle\Exception\SmartyStreetsException;
use RentTrack\SmartyStreetsBundle\Model\International\InternationalAddress;
use RentTrack\SmartyStreetsBundle\Model\International\Components as InternationalComponents;
use RentTrack\SmartyStreetsBundle\Model\International\Metadata as InternationalMetadata;
use RentTrack\SmartyStreetsBundle\Model\US\Components as USComponents;
use RentTrack\SmartyStreetsBundle\Model\US\Metadata as USMetadata;
use RentTrack\SmartyStreetsBundle\Model\US\USAddress;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class SmartyStreetsAddressLookupServiceCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException
     * @expectedExceptionMessage [SmartyStreetsAddressLookupService] Address not found : test
     */
    public function shouldThrowExceptionIfSmartyStreetsThrowException()
    {
        $ssClient = $this->getSmartyStreetsClientMock();
        $ssClient->expects($this->once())
            ->method('getUSAddress')
            ->with($this->equalTo('test'), $this->equalTo('test'), $this->equalTo('test'), $this->equalTo('test'))
            ->will($this->throwException(new SmartyStreetsException('test')));

        $ssAddressLookupService = new SmartyStreetsAddressLookupService(
            $ssClient,
            $this->getValidatorMock(),
            $this->getLoggerMock()
        );
        $ssAddressLookupService->lookup('test', 'test', 'test', 'test');
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException
     * @expectedExceptionMessage [SmartyStreetsAddressLookupService] Country "badCountry" not supported.
     */
    public function shouldThrowExceptionIfInputCountryNotSupported()
    {
        $ssAddressLookupService = new SmartyStreetsAddressLookupService(
            $this->getSmartyStreetsClientMock(),
            $this->getValidatorMock(),
            $this->getLoggerMock()
        );
        $ssAddressLookupService->lookup('test', 'test', 'test', 'test', 'badCountry');
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException
     * @expectedExceptionMessage [SmartyStreetsAddressLookupService] SmartyStreets returned invalid address
     */
    public function shouldThrowExceptionIfSmartyStreetsReturnInvalidAddress()
    {
        $response = new USAddress();
        $response->setMetadata(new USMetadata());
        $response->setComponents(new USComponents());

        $ssClient = $this->getSmartyStreetsClientMock();
        $ssClient->expects($this->once())
            ->method('getUSAddress')
            ->with($this->equalTo('test'), $this->equalTo('test'), $this->equalTo('test'), $this->equalTo('test'))
            ->will($this->returnValue($response));

        $constraintViolationList = new ConstraintViolationList();
        $constraintViolationList->add(new ConstraintViolation('test', '', [], '', '', ''));

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->will($this->returnValue($constraintViolationList));

        $ssAddressLookupService = new SmartyStreetsAddressLookupService(
            $ssClient,
            $validator,
            $this->getLoggerMock()
        );
        $ssAddressLookupService->lookup('test', 'test', 'test', 'test');
    }

    /**
     * @test
     */
    public function shouldReturnAddressIfSmartyStreetsReturnValidAddress()
    {
        $response = new USAddress();
        $metadata = new USMetadata();
        $metadata->setLatitude('test');
        $metadata->setLongitude('test');
        $response->setMetadata($metadata);
        $components = new USComponents();
        $components->setPrimaryNumber('test');
        $components->setStreetName('test');
        $components->setStreetSuffix('test');
        $components->setZipCode('test');
        $components->setCityName('test');
        $components->setStateAbbreviation('test');
        $response->setComponents($components);

        $ssClient = $this->getSmartyStreetsClientMock();
        $ssClient->expects($this->once())
            ->method('getUSAddress')
            ->with($this->equalTo('test'), $this->equalTo('test'), $this->equalTo('test'), $this->equalTo('test'))
            ->will($this->returnValue($response));

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));

        $ssAddressLookupService = new SmartyStreetsAddressLookupService(
            $ssClient,
            $validator,
            $this->getLoggerMock()
        );
        $address = $ssAddressLookupService->lookup('test', 'test', 'test', 'test');

        $this->assertInstanceOf('\RentJeeves\CoreBundle\Services\AddressLookup\Model\Address', $address);
    }

    /**
     * @test
     */
    public function shouldReturnAddressForInternational()
    {
        $response = new InternationalAddress();

        $addressMetadata = new InternationalMetadata();
        $addressMetadata->setLatitude(0);
        $addressMetadata->setLongitude(0);

        $addressComponents = new InternationalComponents();
        $addressComponents->setPremiseNumber(1);
        $addressComponents->setThoroughfare('test');
        $addressComponents->setPostalCode('test');
        $addressComponents->setLocality('test');
        $addressComponents->setCountryISO('CA');
        $addressComponents->setAdministrativeArea('test');
        $addressComponents->setSubBuildingNumber(1);

        $response->setMetadata($addressMetadata);
        $response->setComponents($addressComponents);

        $ssClient = $this->getSmartyStreetsClientMock();
        $ssClient->expects($this->once())
            ->method('getInternationalAddress')
            ->with(
                $this->equalTo('test'),
                $this->equalTo('test'),
                $this->equalTo('test'),
                $this->equalTo('test'),
                $this->equalTo('CA')
            )
            ->will($this->returnValue($response));

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));

        $ssAddressLookupService = new SmartyStreetsAddressLookupService(
            $ssClient,
            $validator,
            $this->getLoggerMock()
        );
        $address = $ssAddressLookupService->lookup('test', 'test', 'test', 'test', 'CAN');

        $this->assertInstanceOf(Address::class, $address);
    }

    /**
     * @test
     */
    public function shouldReturnAddressIfLookupFreeForm()
    {
        $freeFormAddress = '3839 Hunsaker Dr, East Lansing, MI, United States';
        $freeFormAddressReturn = '3839 Hunsaker Dr, East Lansing, MI';

        $response = new USAddress();
        $metadata = new USMetadata();
        $metadata->setLatitude('test');
        $metadata->setLongitude('test');
        $response->setMetadata($metadata);
        $components = new USComponents();
        $components->setPrimaryNumber('test');
        $components->setStreetName('test');
        $components->setStreetSuffix('test');
        $components->setZipCode('test');
        $components->setCityName('test');
        $components->setStateAbbreviation('test');
        $response->setComponents($components);

        $ssClient = $this->getSmartyStreetsClientMock();
        $ssClient->expects($this->once())
            ->method('getUSAddress')
            ->with($freeFormAddressReturn, '', '', '')
            ->will($this->returnValue($response));

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));

        $ssAddressLookupService = new SmartyStreetsAddressLookupService(
            $ssClient,
            $validator,
            $this->getLoggerMock()
        );
        $address = $ssAddressLookupService->lookupFreeform($freeFormAddress);

        $this->assertInstanceOf(Address::class, $address);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentTrack\SmartyStreetsBundle\SmartyStreetsClient
     */
    protected function getSmartyStreetsClientMock()
    {
        return $this->getMock('\RentTrack\SmartyStreetsBundle\SmartyStreetsClient', [], [], '', false);
    }
}
