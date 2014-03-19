<?php

namespace RentJeeves\CoreBundle\Report;

use JMS\Serializer\Annotation as Serializer;
use DateTime;

class ExperianRentalReport
{
    protected $header;
    protected $records;

    /**
     * @Serializer\Exclude
     */
    protected $container;

    public function __construct($container, $startDate, $endDate)
    {
        $this->container = $container;
        $this->createHeader();
        $this->createRecords($startDate, $endDate);
    }

    protected function createHeader()
    {
        $this->header = new ExperianReportHeader();
    }

    protected function createRecords($startDate, $endDate)
    {
        $this->records = array();
        $contracts = $this->container->get('doctrine.orm.entity_manager')
            ->getRepository('RjDataBundle:Contract')->getContractsForRentalReport($startDate, $endDate);

        foreach ($contracts as $contract) {
            $this->records[] = new ExperianReportRecord($contract);
        }
    }
}
