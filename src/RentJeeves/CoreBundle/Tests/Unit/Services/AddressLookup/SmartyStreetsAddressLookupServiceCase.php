<?php
namespace RentJeeves\CoreBundle\Tests\Unit\Services;

use RentJeeves\CoreBundle\Services\AddressLookup\SmartyStreetsAddressLookupService;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentTrack\SmartyStreetsBundle\Exception\SmartyStreetsException;
use RentTrack\SmartyStreetsBundle\Model\Components;
use RentTrack\SmartyStreetsBundle\Model\Metadata;
use RentTrack\SmartyStreetsBundle\Model\SmartyStreetsAddress;

class SmartyStreetsAddressLookupServiceCase extends BaseTestCase
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
            ->method('getAddress')
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
     * @expectedExceptionMessage [SmartyStreetsAddressLookupService] SmartyStreets returned invalid address
     */
    public function shouldThrowExceptionIfSmartyStreetsReturnInvalidAddress()
    {
        $response = new SmartyStreetsAddress();
        $response->setMetadata(new Metadata());
        $response->setComponents(new Components());

        $ssClient = $this->getSmartyStreetsClientMock();
        $ssClient->expects($this->once())
            ->method('getAddress')
            ->with($this->equalTo('test'), $this->equalTo('test'), $this->equalTo('test'), $this->equalTo('test'))
            ->will($this->returnValue($response));

        $ssAddressLookupService = new SmartyStreetsAddressLookupService(
            $ssClient,
            $this->getValidator(),
            $this->getLoggerMock()
        );
        $ssAddressLookupService->lookup('test', 'test', 'test', 'test');
    }

    /**
     * @test
     */
    public function shouldReturnAddressIfSmartyStreetsReturnValidAddress()
    {
        $response = new SmartyStreetsAddress();
        $metadata = new Metadata();
        $metadata->setLatitude('test');
        $metadata->setLongitude('test');
        $response->setMetadata($metadata);
        $components = new Components();
        $components->setPrimaryNumber('test');
        $components->setStreetName('test');
        $components->setStreetSuffix('test');
        $components->setZipCode('test');
        $components->setCityName('test');
        $components->setStateAbbreviation('test');
        $response->setComponents($components);

        $ssClient = $this->getSmartyStreetsClientMock();
        $ssClient->expects($this->once())
            ->method('getAddress')
            ->with($this->equalTo('test'), $this->equalTo('test'), $this->equalTo('test'), $this->equalTo('test'))
            ->will($this->returnValue($response));

        $ssAddressLookupService = new SmartyStreetsAddressLookupService(
            $ssClient,
            $this->getValidator(),
            $this->getLoggerMock()
        );
        $address = $ssAddressLookupService->lookup('test', 'test', 'test', 'test');

        $this->assertInstanceOf('\RentJeeves\CoreBundle\Services\AddressLookup\Model\Address', $address);
    }

    /**
     * @test
     */
    public function shouldReturnAddressIfLookupAddressByFreeForm()
    {
        $freeFormAddress = '3839 Hunsaker Dr, East Lansing, MI 48823, United States';

        $ssAddressLookupService = $this->getSmartyStreetsClient();
        $address = $ssAddressLookupService->lookupAddressByFreeForm($freeFormAddress);

        $this->assertInstanceOf('\RentJeeves\CoreBundle\Services\AddressLookup\Model\Address', $address);
    }

    /**
     * @return \Symfony\Component\Validator\Validator
     */
    protected function getValidator()
    {
        return $this->getContainer()->get('validator');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentTrack\SmartyStreetsBundle\SmartyStreetsClient
     */
    protected function getSmartyStreetsClientMock()
    {
        return $this->getMock('\RentTrack\SmartyStreetsBundle\SmartyStreetsClient', [], [], '', false);
    }

    /**
     * @return SmartyStreetsAddressLookupService
     */
    protected function getSmartyStreetsClient()
    {
        return $this->getContainer()->get('address_lookup_service.smarty_streets');
    }
}
