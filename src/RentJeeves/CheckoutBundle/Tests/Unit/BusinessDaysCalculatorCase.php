<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit;

use RentJeeves\CheckoutBundle\Payment\BusinessDaysCalculator;
use ReflectionClass;
use DateTime;

class BusinessDaysCalculatorCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider provideNextParameters
     */
    public function shouldCalcNextBusinessDate($startDate, $expectedBusinessDate)
    {
        $calc = new BusinessDaysCalculator();

        $result = $calc->getNextBusinessDate($startDate);

        $this->assertEquals($expectedBusinessDate, $result);
    }

    public function provideNextParameters()
    {
        return [
            [new DateTime("2014-12-22"), new DateTime("2014-12-23")], // Mon -> Tue
            [new DateTime("2014-12-23"), new DateTime("2014-12-24")], // Tue -> Wed
            [new DateTime("2014-12-24"), new DateTime("2014-12-25")], // Wed -> Thu
            [new DateTime("2014-12-25"), new DateTime("2014-12-26")], // Thu -> Fri
            [new DateTime("2014-12-26"), new DateTime("2014-12-29")], // Fri -> Mon
            [new DateTime("2014-12-27"), new DateTime("2014-12-29")], // Sat -> Mon
            [new DateTime("2014-12-28"), new DateTime("2014-12-29")], // Sun -> Mon
            [new DateTime("2014-12-29"), new DateTime("2014-12-30")], // Mon -> Tue
        ];
    }
}
