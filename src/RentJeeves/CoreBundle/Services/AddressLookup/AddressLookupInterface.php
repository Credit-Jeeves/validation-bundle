<?php
namespace RentJeeves\CoreBundle\Services\AddressLookup;

use RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException;
use RentJeeves\CoreBundle\Services\AddressLookup\Model\Address;

interface AddressLookupInterface
{
    const DEFAULT_COUNTRY = 'US';

    /**
     * @param string $street
     * @param string $city
     * @param string $state
     * @param string $zipCode
     * @param string $country
     *
     * @throws AddressLookupException API returned empty response|API returned not valid address|
     * problem with connecting or getting a response from the external lookup API
     *
     * @return Address
     */
    public function lookup($street, $city, $state, $zipCode, $country = self::DEFAULT_COUNTRY);

    /**
     * @param string $address
     * @param string $country
     *
     * @throws AddressLookupException API returned empty response|API returned not valid address|
     * problem with connecting or getting a response from the external lookup API
     *
     * @return Address
     */
    public function lookupFreeform($address, $country = self::DEFAULT_COUNTRY);
}
