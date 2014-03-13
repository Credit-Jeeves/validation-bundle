<?php

namespace RentJeeves\CoreBundle\Report\TransUnion;

use JMS\Serializer\Annotation as Serializer;
use DateTime;

class TransUnionRentalReport
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

    public function getReport()
    {
        $this->createHeader();
        $this->createRecords();
    }

    protected function createHeader()
    {
        $this->header = new ReportHeader();
        $lastActivityDate = $this->container->get('doctrine.orm.entity_manager')
            ->getRepository('RjDataBundle:Contract')->getLastActivityDate();
        $this->header->setActivityDate(new DateTime($lastActivityDate));
    }

    protected function createRecords($startDate, $endDate)
    {
        $this->records = array();
        $contracts = $this->container->get('doctrine.orm.entity_manager')
            ->getRepository('RjDataBundle:Contract')->getContractsForTURentalReport($startDate, $endDate);

        foreach ($contracts as $contract) {
            $this->records[] = new ReportRecord($contract);
        }
    }
}
