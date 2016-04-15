<?php

namespace RentJeeves\CoreBundle\Report\Equifax;

use RentJeeves\CoreBundle\Report\TransUnion\TransUnionReportRecord;

/**
 * EquifaxReportRecord should be the same as TransUnionReportRecord.
 */
class EquifaxReportRecord extends TransUnionReportRecord
{
    /**
     * Equifax wants everything reported as RENTTRACK
     */
    public function getPropertyIdentificationNumber()
    {
        return str_pad('RENTTRACK', 20);
    }
}
