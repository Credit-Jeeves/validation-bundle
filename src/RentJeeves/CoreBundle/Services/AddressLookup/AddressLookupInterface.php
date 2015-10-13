<?php
namespace RentJeeves\CoreBundle\Services\AddressLookup;

use RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupEmptyResponseException;
use RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupUnavailableException;
use RentJeeves\CoreBundle\Services\AddressLookup\Model\Address;

interface AddressLookupInterface
{
    /**
     * @param string $street
     * @param string $city
     * @param string $state
     * @param string $zipCode
     *
     * @throws AddressLookupEmptyResponseException API returned empty response
     *
     * @throws AddressLookupUnavailableException problem with connecting or getting a response
     * from the external lookup API
     *
     * @return Address
     */
    public function lookup($street, $city, $state, $zipCode);
}
