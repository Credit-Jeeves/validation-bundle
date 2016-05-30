<?php

namespace RentJeeves\ImportBundle\Tests\Unit\Helper;

use RentJeeves\ImportBundle\Helper\LeaseEndDateCalculator;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class LeaseEndDateCalculatorCase extends UnitTestBase
{
    /**
     * @return array
     */
    public function providerForCalculateFinishAt()
    {
        $leaseEnd1 = \DateTime::createFromFormat('Y-m-d', '2012-09-12');
        $moveOut1 = \DateTime::createFromFormat('Y-m-d', '2011-09-12');
        $leaseEnd2 = new \DateTime();
        $leaseEnd2->setTime(0, 0, 0);

        return [
            [null, null, null, null, null],
            [$leaseEnd1, null, null, null, $leaseEnd1],
            [null, null, 'c', 'y', null],
            [$leaseEnd1, $moveOut1, null, null, $moveOut1],
            [$leaseEnd2, $leaseEnd2, null, 'y', $leaseEnd2],
            [$leaseEnd2, null, null, 'n', $leaseEnd2],
            [$leaseEnd1, null, null, 'y', null]
        ];
    }

    /**
     * @test
     * @dataProvider providerForCalculateFinishAt
     *
     * @param \DateTime|null $leaseEnd
     * @param \DateTime|null $moveOut
     * @param string|null $tenantStatus
     * @param string|null $monthToMonth
     * @param mixed $result
     */
    public function shouldCalculateFinishAt(
        \DateTime $leaseEnd = null,
        \DateTime $moveOut = null,
        $tenantStatus = null,
        $monthToMonth = null,
        $result = null
    )
    {
        $finishAt = LeaseEndDateCalculator::calculateFinishAt($leaseEnd, $moveOut, $tenantStatus, $monthToMonth);
        $this->assertEquals($result, $finishAt, 'Should be the same');
    }
}
