<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit;

use RentJeeves\CheckoutBundle\Payment\BusinessDaysCalculator;
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

    /**
     * @test
     * @dataProvider provideBusinessDates
     */
    public function shouldCalcBusinessDate($startDate, $targetShift, $expectedBusinessDate)
    {
        $calc = new BusinessDaysCalculator();

        $result = $calc->getBusinessDate($startDate, $targetShift);

        $this->assertEquals($expectedBusinessDate, $result);
    }

    /**
     * @return array
     */
    public function provideBusinessDates()
    {
        return [
            [new DateTime("2015-04-13"), 3, new DateTime("2015-04-16")], // Mon + 3 = Thu
            [new DateTime("2015-04-13"), 4, new DateTime("2015-04-17")], // Mon + 4 = Fr
            [new DateTime("2015-04-13"), 5, new DateTime("2015-04-20")], // Mon + 5 = Mon
            [new DateTime("2015-04-16"), 1, new DateTime("2015-04-17")], // Thu + 1 = Fr
            [new DateTime("2015-04-16"), 2, new DateTime("2015-04-20")], // Thu + 2 = Mon
            [new DateTime("2015-04-16"), 3, new DateTime("2015-04-21")], // Thu + 3 = Tue
            [new DateTime("2015-04-13"), 10, new DateTime("2015-04-27")], // Mon + 10 = Mon
            [new DateTime("2015-04-13"), 14, new DateTime("2015-05-01")], // Mon + 14 = Fr
        ];
    }
}
