<?php

namespace RentJeeves\TestBundle\Report;

use RentJeeves\CoreBundle\Report\RentalReportData;

class RentalReportDataManager
{
    /**
     * Supplies an easy getter for RentalReportData.
     *
     * @param \DateTime $month
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param string $bureau
     * @param string $type
     *
     * @return RentalReportData
     */
    public static function getRentalReportData(
        \DateTime $month,
        \DateTime $startDate,
        \DateTime $endDate,
        $bureau,
        $type
    ) {
        $data = new RentalReportData();
        $data->setMonth($month);
        $data->setStartDate($startDate);
        $data->setEndDate($endDate);
        $data->setBureau($bureau);
        $data->setType($type);

        return $data;
    }
}
