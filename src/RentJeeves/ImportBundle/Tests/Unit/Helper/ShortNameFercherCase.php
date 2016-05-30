<?php

namespace RentJeeves\ImportBundle\Tests\Unit\Helper;

use RentJeeves\ImportBundle\Helper\ShortNameFetcher;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class ShortNameFercherCase extends UnitTestBase
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
        ];
    }

    /**
     * @test
     * @dataProvider sanitizeTenantNameProvider
     *
     * @param string $name
     * @param string $expectedFirstName
     * @param string $expectedLastName
     */
    public function shouldGetFirstAndLastName($name, $expectedFirstName, $expectedLastName)
    {
        $this->assertEquals(
            $expectedFirstName,
            ShortNameFetcher::extractFirstName($name),
            'First name mapped not correctly'
        );

        $this->assertEquals(
            $expectedLastName,
            ShortNameFetcher::extractLastName($name),
            'Last name mapped not correctly'
        );
    }
}

