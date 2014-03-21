<?php

namespace RentJeeves\CoreBundle\Report;

use JMS\Serializer\Annotation as Serializer;
use DateTime;

class ExperianRentalReport
{
    protected $records;

    /**
     * @Serializer\Exclude
     */
    protected $em;

    public function __construct($em, $reportMonth, $reportYear)
    {
        $this->em = $em;
        $this->createRecords($reportMonth, $reportYear);
    }

    protected function createRecords($reportMonth, $reportYear)
    {
        $this->records = array();
        $contractRepo = $this->em->getRepository('RjDataBundle:Contract');

        $contracts = $contractRepo->getContractsForRentalReport($reportMonth, $reportYear);

        foreach ($contracts as $contract) {
            $rentOperation = $contractRepo->getRentOperationForMonth($contract->getId(), $reportMonth, $reportYear);
            $this->records[] = new ExperianReportRecord($contract, $rentOperation);
        }
    }
}
