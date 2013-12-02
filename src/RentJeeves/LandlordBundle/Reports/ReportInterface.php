<?php

namespace RentJeeves\LandlordBundle\Reports;

use Symfony\Component\HttpFoundation\Response;

interface ReportInterface
{
    /**
     * @param $orders
     * @param $begin
     * @param $end
     *
     * @return Response
     */
    public function getReport($orders, $begin, $end);
}
