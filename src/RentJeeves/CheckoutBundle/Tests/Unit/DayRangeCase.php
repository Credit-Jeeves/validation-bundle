<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit;

use RentJeeves\CheckoutBundle\Constraint\DayRange;
use RentJeeves\TestBundle\BaseTestCase;

class DayRangeCase extends BaseTestCase
{

    public function getData()
    {
        return array(
            array($openDay = 5, $closeDay = 22, $todayDay = '2014-08-17', $isValid = true),
            array($openDay = 22, $closeDay = 2, $todayDay = '2014-08-22', $isValid = true),
            array($openDay = 5, $closeDay = 22, $todayDay = '2014-08-23', $isValid = false),
            array($openDay = 22, $closeDay = 2, $todayDay = '2014-08-03', $isValid = false),
            array($openDay = 17, $closeDay = 22, $todayDay = '2014-08-17', $isValid = true),
            array($openDay = 21, $closeDay = 22, $todayDay = '2014-08-22', $isValid = true),
            array($openDay = 12, $closeDay = 10, $todayDay = '2014-08-11', $isValid = false),
            array($openDay = 15, $closeDay = 20, $todayDay = '2014-08-14', $isValid = false),
            array($openDay = 15, $closeDay = 20, $todayDay = '2014-08-15', $isValid = true),
            array($openDay = 22, $closeDay = 2, $todayDay = '2014-08-22', $isValid = true),
        );
    }

    /**
     * @test
     * @dataProvider getData
     */
    public function dayRange($openDay, $closeDay, $today, $isValid)
    {
        $container = $this->getKernel()->getContainer();
        $errorList = $container->get('validator')->validateValue(
            $today,
            new DayRange(
                array(
                    'translator' => $container->get('translator'),
                    'openDay'    => $openDay,
                    'closeDay'   => $closeDay
                )
            )
        );

        if ($isValid) {
            $this->assertTrue(count($errorList) === 0);
        } else {
            $this->assertTrue(count($errorList) === 1);
        }

    }
}
