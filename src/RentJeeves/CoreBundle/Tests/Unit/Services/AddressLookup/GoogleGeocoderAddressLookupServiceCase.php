<?php
namespace RentJeeves\CoreBundle\Tests\Unit\Services;

use Geocoder\Result\Geocoded;
use RentJeeves\CoreBundle\Services\AddressLookup\GoogleGeocoderAddressLookupService;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class GoogleGeocoderAddressLookupServiceCase extends BaseTestCase
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

        $GoogleGeocoderAddressLookupService = new GoogleGeocoderAddressLookupService(
            $geocoder,
            $this->getValidatorMock(),
            $this->getLoggerMock()
        );
        $GoogleGeocoderAddressLookupService->lookup('test', 'test', 'test', 'test');
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

        $GoogleGeocoderAddressLookupService = new GoogleGeocoderAddressLookupService(
            $geocoder,
            $this->getValidatorMock(),
            $this->getLoggerMock()
        );
        $GoogleGeocoderAddressLookupService->lookup('test', 'test', 'test', 'test');
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

        $GoogleGeocoderAddressLookupService = new GoogleGeocoderAddressLookupService(
            $geocoder,
            $this->getValidator(),
            $this->getLoggerMock()
        );
        $GoogleGeocoderAddressLookupService->lookup('test', 'test', 'test', 'test');
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

        $GoogleGeocoderAddressLookupService = new GoogleGeocoderAddressLookupService(
            $geocoder,
            $this->getValidator(),
            $this->getLoggerMock()
        );
        $address = $GoogleGeocoderAddressLookupService->lookup('test', 'test', 'test', 'test');

        $this->assertInstanceOf('\RentJeeves\CoreBundle\Services\AddressLookup\Model\Address', $address);
    }

    /**
     * @return \Geocoder\Geocoder
     */
    protected function getGeocoder()
    {
        return $this->getContainer()->get('bazinga_geocoder.geocoder');
    }

    /**
     * @return \Symfony\Component\Validator\Validator
     */
    protected function getValidator()
    {
        return $this->getContainer()->get('validator');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Geocoder\Geocoder
     */
    protected function getGeocoderMock()
    {
        return $this->getMock('\Geocoder\Geocoder', ['geocode'], [], '', false);
    }
}
