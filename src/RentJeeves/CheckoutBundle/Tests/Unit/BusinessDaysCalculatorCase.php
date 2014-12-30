<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit;

use RentJeeves\CheckoutBundle\Payment\BusinessDaysCalculator;
use ReflectionClass;
use DateTime;

class BusinessDaysCalculatorCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeConstructedWithCCAndACHBusinessDays()
    {
        $rc = new ReflectionClass('RentJeeves\CheckoutBundle\Payment\BusinessDaysCalculator');
        $instance = $rc->newInstanceArgs(array('1', '3'));

        $ccField = $rc->getProperty('ccBusinessDays');
        $ccField->setAccessible(true);
        $achField = $rc->getProperty('achBusinessDays');
        $achField->setAccessible(true);

        $this->assertEquals(1, $ccField->getValue($instance));
        $this->assertEquals(3, $achField->getValue($instance));
    }

    /**
     * @test
     * @dataProvider provideCreditCardsShiftParameters
     */
    public function shouldCalcCreditCardBusinessDays($startDate, $shiftCc, $shiftAch, $expectedBusinessDate)
    {
        $calc = new BusinessDaysCalculator($shiftCc, $shiftAch);

        $result = $calc->getCreditCardBusinessDate($startDate);

        $this->assertEquals($expectedBusinessDate, $result);
    }

    /**
     * @test
     * @dataProvider provideACHShiftParameters
     */
    public function shouldCalcACHBusinessDays($startDate, $shiftCc, $shiftAch, $expectedBusinessDate)
    {
        $calc = new BusinessDaysCalculator($shiftCc, $shiftAch);

        $result = $calc->getACHBusinessDate($startDate);

        $this->assertEquals($expectedBusinessDate, $result);
    }

    /**
     * @test
     * @dataProvider provideRandomShiftParameters
     */
    public function shouldCalcBusinessDateWithRandomShift($startDate, $shift, $expectedBusinessDate)
    {
        $calc = new BusinessDaysCalculator(1, 3);

        $result = $calc->getBusinessDate($startDate, $shift);

        $this->assertEquals($expectedBusinessDate, $result);
    }

    /**
     * @test
     * @dataProvider provideNextParameters
     */
    public function shouldCalcNextBusinessDate($startDate, $expectedBusinessDate)
    {
        $calc = new BusinessDaysCalculator(0, 0);

        $result = $calc->getNextBusinessDate($startDate);

        $this->assertEquals($expectedBusinessDate, $result);
    }

    public function provideCreditCardsShiftParameters()
    {
        return array(
            array(new DateTime("2014-02-07"), 1, 3, new DateTime('2014-02-10')),
            array(new DateTime("2014-02-10"), 1, 3, new DateTime('2014-02-11')),
            array(new DateTime("2014-02-09"), 1, 3, new DateTime('2014-02-11')),
            array(new DateTime("2014-02-08"), 1, 3, new DateTime('2014-02-11')),
        );
    }

    public function provideACHShiftParameters()
    {
        return array(
            array(new DateTime("2014-02-07"), 1, 3, new DateTime('2014-02-12')),
            array(new DateTime("2014-02-10"), 1, 3, new DateTime('2014-02-13')),
            array(new DateTime("2014-02-09"), 1, 3, new DateTime('2014-02-13')),
            array(new DateTime("2014-02-08"), 1, 3, new DateTime('2014-02-13')),
        );
    }

    public function provideRandomShiftParameters()
    {
        return array(
            array(new DateTime("2014-02-07"), 1, new DateTime('2014-02-10')),
            array(new DateTime("2014-02-10"), 2, new DateTime('2014-02-12')),
            array(new DateTime("2014-02-09"), 3, new DateTime('2014-02-13')),
            array(new DateTime("2014-02-08"), 4, new DateTime('2014-02-14')),
            array(new DateTime("2014-02-08"), 5, new DateTime('2014-02-17')),
        );
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
