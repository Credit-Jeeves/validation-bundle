<?php

namespace RentJeeves\CoreBundle\Tests\Unit\Report\TransUnion;

use RentJeeves\CoreBundle\Report\TransUnion\TransUnionReportRecord;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class TransUnionReportRecordCase extends UnitTestBase
{
    /**
     * @test
     */
    public function shouldFillRentalHistoryProfileWithBIfReportedMonthLessThanLeaseStart()
    {
        $contract = new Contract();
        $contract->setStartAt(new \DateTime('2016-06-01'));
        $reportedMonth = new \DateTime('2013-06-01');

        $reportRecord = new TransUnionReportRecord($contract, $reportedMonth, new \DateTime());
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

        $reportRecord = new TransUnionReportRecord($contract, $reportedMonth, new \DateTime());
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
        $reportedMonth = new \DateTime('2016-05-17');

        $reportRecord = new TransUnionReportRecord($contract, $reportedMonth, new \DateTime());
        $this->assertEquals(
            '00000BBBBBBBBBBBBBBBBBBB',
            $reportRecord->getRentalHistoryProfile(),
            'RentalHistoryProfile should have 5 zeros and 19 B\'s'
        );
    }
}
