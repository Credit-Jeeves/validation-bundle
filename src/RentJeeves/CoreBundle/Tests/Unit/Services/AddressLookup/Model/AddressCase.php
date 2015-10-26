<?php

namespace RentJeeves\CoreBundle\Tests\Unit\Services\AddressLookup\Model;

use RentJeeves\CoreBundle\Services\AddressLookup\Model\Address;

class AddressCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getAddresses()
    {
        return [
            [
                [
                    'Number' => '360',
                    'Street' => 'N Bedford Dr',
                    'City' => 'Beverly Hills',
                    'State' => 'CA',
                    'Longitude' => 1.1,
                    'Latitude' => 2
                ],
                '360NBedfordDrBeverlyHillsCA'
            ],
            [
                [
                    'Number' => '770',
                    'Street' => 'Broadway',
                    'City' => 'New York',
                    'State' => 'NY',
                    'Longitude' => 1,
                    'Latitude' => 2.2
                ],
                '770BroadwayNewYorkNY'
            ],
            [
                [
                    'Number' => '770',
                    'Street' => 'Broadway',
                    'City' => 'New York',
                    'State' => 'NY',
                    'Longitude' => null,
                    'Latitude' => null
                ],
                null
            ]
        ];
    }

    /**
     * @param array $addressArray
     * @param string $expectedIndex
     *
     * @test
     * @dataProvider getAddresses
     */
    public function shouldReturnCorrectIndex(array $addressArray, $expectedIndex)
    {
        $address = new Address();
        $address->setNumber($addressArray['Number']);
        $address->setStreet($addressArray['Street']);
        $address->setCity($addressArray['City']);
        $address->setState($addressArray['State']);
        $address->setLongitude($addressArray['Longitude']);
        $address->setLatitude($addressArray['Latitude']);

        $this->assertEquals($expectedIndex, $address->getIndex(), 'Index formed incorrectly');
    }
}
