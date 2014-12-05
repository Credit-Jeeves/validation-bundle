<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit;

use RentJeeves\CheckoutBundle\Constraint\DayRange;
use RentJeeves\TestBundle\BaseTestCase;

class DayRangeCase extends BaseTestCase
{

    public function getData()
    {
        return array(
            array($openDay = 5, $closeDay = 22, $todayDay = '2014-08-17', $errorsCount = 0),
            array($openDay = 22, $closeDay = 2, $todayDay = '2014-08-22', $errorsCount = 0),
            array($openDay = 5, $closeDay = 22, $todayDay = '2014-08-23', $errorsCount = 1),
            array($openDay = 22, $closeDay = 2, $todayDay = '2014-08-03', $errorsCount = 1),
            array($openDay = 17, $closeDay = 22, $todayDay = '2014-08-17', $errorsCount = 0),
            array($openDay = 21, $closeDay = 22, $todayDay = '2014-08-22', $errorsCount = 0),
            array($openDay = 12, $closeDay = 10, $todayDay = '2014-08-11', $errorsCount = 1),
            array($openDay = 15, $closeDay = 20, $todayDay = '2014-08-14', $errorsCount = 1),
            array($openDay = 15, $closeDay = 20, $todayDay = '2014-08-15', $errorsCount = 0),
            array($openDay = 22, $closeDay = 2, $todayDay = '2014-08-22', $errorsCount = 0),
            array($openDay = 27, $closeDay = 2, $todayDay = '2014-08-01', $errorsCount = 0),
            array($openDay = 27, $closeDay = 2, $todayDay = '2014-08-03', $errorsCount = 1),
        );
    }

    /**
     * @test
     * @dataProvider getData
     */
    public function dayRange($openDay, $closeDay, $today, $errorsCount)
    {
        $container = $this->getKernel()->getContainer();
        $errorList = $container->get('validator')->validateValue(
            $today,
            new DayRange(
                array(
                    'openDay'    => $openDay,
                    'closeDay'   => $closeDay
                )
            )
        );

        $this->assertEquals(count($errorList), $errorsCount);
    }
}
