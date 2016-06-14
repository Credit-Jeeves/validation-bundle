<?php

namespace RentJeeves\ImportBundle\Tests\Unit\Helper;

use RentJeeves\ImportBundle\Helper\ShortNameFetcher;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class ShortNameFetcherCase extends UnitTestBase
{
    /**
     * @return array
     */
    public function shouldGetFirstAndLastNameProvider()
    {
        return [
            ['Bob Damian and Marley Denial', 'Bob', 'Damian'],
            ['Bob & Damian & Marley', 'Bob', 'Marley'],
            ['Bob Damian & Marley Denial', 'Bob', 'Damian'],
            ['Jerry J. Garcia', 'Jerry', 'Garcia'],
            ['Martin Luther King Jr.', 'Martin', 'King'],
            ['Dr. Ruth Westheimer', 'Ruth', 'Westheimer'],
            ['Bob & Damian Marley', 'Bob', 'Marley'],
            ['Jerry J. Garcia J.', 'Jerry', 'Garcia'],
            ['Bob Damian Marley Denial', 'Bob', 'Denial'],
            ['Bob and Damian Marley', 'Bob', 'Marley'],
            [null, null, null]
        ];
    }

    /**
     * @test
     * @dataProvider shouldGetFirstAndLastNameProvider
     *
     * @param string $fullName
     * @param string $expectedFirstName
     * @param string $expectedLastName
     */
    public function shouldGetFirstAndLastName($fullName, $expectedFirstName, $expectedLastName)
    {
        $this->assertEquals(
            $expectedFirstName,
            ShortNameFetcher::extractFirstName($fullName),
            'First name mapped not correctly'
        );

        $this->assertEquals(
            $expectedLastName,
            ShortNameFetcher::extractLastName($fullName),
            'Last name mapped not correctly'
        );
    }
}
