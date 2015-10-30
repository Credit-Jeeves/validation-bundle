<?php

namespace RentJeeves\ComponentBundle\Tests\Helper;

use RentJeeves\ComponentBundle\Helper\DateCheckerHelper;
use RentJeeves\TestBundle\BaseTestCase;

class DateCheckerHelperCase extends BaseTestCase
{
    /**
     * @return array
     */
    public function dateProvider()
    {
        return [
            [$startDate = new \DateTime('-1 day'), $endDate = new \DateTime(), true],
            [$startDate = new \DateTime('+1 day'), $endDate = new \DateTime(), false],
            [$startDate = new \DateTime('-1 day'), null, true],
            [null, $endDate = new \DateTime('-1 day'), false],
            [null, $endDate = new \DateTime('+1 day'), true],
            [$startDate = new \DateTime('+1 day'), $endDate = new \DateTime('-1 day'), false],
            [$startDate = new \DateTime('-1 year'), null, true]
        ];
    }

    /**
     * @test
     * @dataProvider dateProvider
     */
    public function shouldCheckDateFallsBetweenDates($startDate, $endDate, $result)
    {
        $resultExecute = DateCheckerHelper::checkDateFallsBetweenDates($startDate, $endDate);

        $this->assertEquals($result, $resultExecute);
    }
}
