<?php

namespace RentJeeves\LandlordBundle\Reports;

use Symfony\Component\HttpFoundation\Response;

interface ReportInterface
{
    /**
     * @return string
     */
    public function getReport();
}
