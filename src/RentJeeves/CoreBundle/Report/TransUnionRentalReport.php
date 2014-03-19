<?php

namespace RentJeeves\CoreBundle\Report;

use JMS\Serializer\Annotation as Serializer;
use DateTime;

class TransUnionRentalReport
{
    protected $header;
    protected $records;

    /**
     * @Serializer\Exclude
     */
    protected $em;

    public function __construct($em, $startDate, $endDate)
    {
        $this->em = $em;
        $this->createHeader();
        $this->createRecords($startDate, $endDate);
    }

    protected function createHeader()
    {
        $this->header = new TransUnionReportHeader();
        $lastActivityDate = $this->em->getRepository('RjDataBundle:Contract')->getLastActivityDate();
        $this->header->setActivityDate(new DateTime($lastActivityDate));
    }

    protected function createRecords($startDate, $endDate)
    {
        $this->records = array();
        $contracts = $this->em
            ->getRepository('RjDataBundle:Contract')->getContractsForRentalReport($startDate, $endDate);

        foreach ($contracts as $contract) {
            $this->records[] = new TransUnionReportRecord($contract);
        }
    }
}
