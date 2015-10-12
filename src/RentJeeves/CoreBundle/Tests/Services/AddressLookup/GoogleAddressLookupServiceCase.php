<?php
namespace RentJeeves\CoreBundle\Tests\Services;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class GoogleAddressLookupServiceCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldReturnAddressIfSendCorrectData()
    {
        $street = '3850 Coleman Road';
        $city = 'East Lansing';
        $state = 'MI';
        $zipCode = '48823';

        $result = $this->getGoogleAddressLookupService()->lookup($street, $city, $state, $zipCode);

        $this->assertInstanceOf('\RentJeeves\CoreBundle\Services\AddressLookup\Model\Address', $result);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException
     * @expectedExceptionMessage 123
     */
    public function shouldThrowExceptionIfSendCorrectDataWithNonexistentUnitNumber()
    {
        $street = '123123123 Coleman Road';
        $city = 'East Lansing';
        $state = 'MI';
        $zipCode = '48823';

        $result = $this->getGoogleAddressLookupService()->lookup($street, $city, $state, $zipCode);

        $this->assertInstanceOf('\RentJeeves\CoreBundle\Services\AddressLookup\Model\Address', $result);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException
     */
    public function shouldThrowExceptionIfSendNotCorrectData()
    {
        $street = 'test';
        $city = 'test';
        $state = 'test';
        $zipCode = 'test';

        $this->getGoogleAddressLookupService()->lookup($street, $city, $state, $zipCode);
    }

    /**
     * @return \RentJeeves\CoreBundle\Services\AddressLookup\GoogleAddressLookupService
     */
    protected function getGoogleAddressLookupService()
    {
        return $this->getContainer()->get('google_address_lookup_servise');
    }
}
