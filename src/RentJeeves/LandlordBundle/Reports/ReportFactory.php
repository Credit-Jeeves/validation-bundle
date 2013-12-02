<?php

namespace RentJeeves\LandlordBundle\Reports;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("report.factory")
 */
class ReportFactory
{
    protected $container;

    /**
     * @InjectParams({
     *     "container"  = @Inject("service_container"),
     * })
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getBaseReportByType($type, $orders, $begin, $end)
    {
        return $this->container->get('base.report.'.$type)->getReport($orders, $begin, $end);
    }
}
