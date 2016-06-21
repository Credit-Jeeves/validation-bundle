<?php

namespace RentJeeves\CoreBundle\Tests\Unit\Report\Equifax;

use RentJeeves\CoreBundle\Report\Equifax\EquifaxReportRecord;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class EquifaxReportRecordCase extends UnitTestBase
{
    /**
     * @test
     */
    public function shouldFillRentalHistoryProfileWithBIfReportedMonthLessThanLeaseStart()
    {
        $contract = new Contract();
        $contract->setStartAt(new \DateTime('2016-06-01'));
        $reportedMonth = new \DateTime('2013-06-01');

        $reportRecord = new EquifaxReportRecord($contract, $reportedMonth, new \DateTime());
        $this->assertEquals(
            'BBBBBBBBBBBBBBBBBBBBBBBB',
            $reportRecord->getRentalHistoryProfile(),
            'RentalHistoryProfile should have 24 B\'s'
        );
    }

    /**
     * @test
     */
    public function shouldFillRentalHistoryProfileWithAllZerosIfReportedMonth24MonthsLaterThanLeaseStart()
    {
        $contract = new Contract();
        $contract->setStartAt(new \DateTime('2014-05-01'));
        $reportedMonth = new \DateTime('2016-06-01');

        $reportRecord = new EquifaxReportRecord($contract, $reportedMonth, new \DateTime());
        $this->assertEquals(
            '000000000000000000000000',
            $reportRecord->getRentalHistoryProfile(),
            'RentalHistoryProfile should have 24 0\'s'
        );
    }

    /**
     * @test
     */
    public function shouldFillRentalHistoryProfileWith3ZerosAndAllBIfReportedMonth3MonthsLaterThanLeaseStart()
    {
        $contract = new Contract();
        $contract->setStartAt(new \DateTime('2016-01-21'));
        $reportedMonth = new \DateTime('2016-03-17');

        $reportRecord = new EquifaxReportRecord($contract, $reportedMonth, new \DateTime());
        $this->assertEquals(
            '000BBBBBBBBBBBBBBBBBBBBB',
            $reportRecord->getRentalHistoryProfile(),
            'RentalHistoryProfile should have 3 zeros and 21 B\'s'
        );
    }
}
