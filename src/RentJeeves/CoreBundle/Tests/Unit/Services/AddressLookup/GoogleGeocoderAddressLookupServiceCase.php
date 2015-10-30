<?php
namespace RentJeeves\CoreBundle\Tests\Unit\Services;

use Geocoder\Result\Geocoded;
use RentJeeves\CoreBundle\Services\AddressLookup\GoogleGeocoderAddressLookupService;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class GoogleGeocoderAddressLookupServiceCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;
    use WriteAttributeExtensionTrait;

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException
     * @expectedExceptionMessage [GoogleGeocoderAddressLookupService] Could not reach Google GeoCode : test
     */
    public function shouldThrowExceptionIfGeocoderThrowException()
    {
        $geocoder = $this->getGeocoderMock();
        $geocoder->expects($this->once())
            ->method('geocode')
            ->with($this->equalTo('test, test, test test'))
            ->will($this->throwException(new \Exception('test')));

        $googleGeocoderAddressLookupService = new GoogleGeocoderAddressLookupService(
            $geocoder,
            $this->getValidatorMock(),
            $this->getLoggerMock()
        );
        $googleGeocoderAddressLookupService->lookup('test', 'test', 'test', 'test');
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException
     * @expectedExceptionMessage [GoogleGeocoderAddressLookupService] Google returned empty response
     */
    public function shouldThrowExceptionIfGeocoderReturnEmptyResponse()
    {
        $geocoder = $this->getGeocoderMock();
        $geocoder->expects($this->once())
            ->method('geocode')
            ->with($this->equalTo('test, test, test test'))
            ->will($this->returnValue(null));

        $googleGeocoderAddressLookupService = new GoogleGeocoderAddressLookupService(
            $geocoder,
            $this->getValidatorMock(),
            $this->getLoggerMock()
        );
        $googleGeocoderAddressLookupService->lookup('test', 'test', 'test', 'test');
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException
     * @expectedExceptionMessage [GoogleGeocoderAddressLookupService] Google returned invalid address
     */
    public function shouldThrowExceptionIfGeocoderReturnInvalidAddress()
    {
        $response = new Geocoded();
        $geocoder = $this->getGeocoderMock();
        $geocoder->expects($this->once())
            ->method('geocode')
            ->with($this->equalTo('test, test, test test'))
            ->will($this->returnValue($response));

        $constraintViolationList = new ConstraintViolationList();
        $constraintViolationList->add(new ConstraintViolation('test','',[],'','',''));

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->will($this->returnValue($constraintViolationList));

        $googleGeocoderAddressLookupService = new GoogleGeocoderAddressLookupService(
            $geocoder,
            $validator,
            $this->getLoggerMock()
        );
        $googleGeocoderAddressLookupService->lookup('test', 'test', 'test', 'test');
    }

    /**
     * @test
     */
    public function shouldReturnAddressIfGeocoderReturnValidAddress()
    {
        $response = new Geocoded();
        $this->writeAttribute($response, 'latitude', 'test_latitude');
        $this->writeAttribute($response, 'longitude', 'test_longitude');
        $this->writeAttribute($response, 'streetNumber', 'test_streetNumber');
        $this->writeAttribute($response, 'streetName', 'test_streetName');
        $this->writeAttribute($response, 'city', 'test_city');
        $this->writeAttribute($response, 'zipcode', 'test_zipcode');
        $this->writeAttribute($response, 'countryCode', 'test_country');
        $this->writeAttribute($response, 'regionCode', 'test_regionCode');

        $geocoder = $this->getGeocoderMock();
        $geocoder->expects($this->once())
            ->method('geocode')
            ->with($this->equalTo('test, test, test test'))
            ->will($this->returnValue($response));

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));

        $googleGeocoderAddressLookupService = new GoogleGeocoderAddressLookupService(
            $geocoder,
            $validator,
            $this->getLoggerMock()
        );
        $address = $googleGeocoderAddressLookupService->lookup('test', 'test', 'test', 'test');

        $this->assertInstanceOf('\RentJeeves\CoreBundle\Services\AddressLookup\Model\Address', $address);
    }

    /**
     * @test
     */
    public function shouldReturnAddressIfLookupAddressByFreeForm()
    {
        $freeFormAddress = '3839 Hunsaker Dr, East Lansing, MI 48823, United States';

        $response = new Geocoded();
        $this->writeAttribute($response, 'latitude', 'test_latitude');
        $this->writeAttribute($response, 'longitude', 'test_longitude');
        $this->writeAttribute($response, 'streetNumber', 'test_streetNumber');
        $this->writeAttribute($response, 'streetName', 'test_streetName');
        $this->writeAttribute($response, 'city', 'test_city');
        $this->writeAttribute($response, 'zipcode', 'test_zipcode');
        $this->writeAttribute($response, 'countryCode', 'test_country');
        $this->writeAttribute($response, 'regionCode', 'test_regionCode');

        $geocoder = $this->getGeocoderMock();
        $geocoder->expects($this->once())
            ->method('geocode')
            ->with($this->equalTo($freeFormAddress))
            ->will($this->returnValue($response));

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));

        $googleGeocoderAddressLookupService = new GoogleGeocoderAddressLookupService(
            $geocoder,
            $validator,
            $this->getLoggerMock()
        );
        $address = $googleGeocoderAddressLookupService->lookupFreeform($freeFormAddress);

        $this->assertInstanceOf('\RentJeeves\CoreBundle\Services\AddressLookup\Model\Address', $address);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Geocoder\Geocoder
     */
    protected function getGeocoderMock()
    {
        return $this->getMock('\Geocoder\Geocoder', ['geocode'], [], '', false);
    }
}
