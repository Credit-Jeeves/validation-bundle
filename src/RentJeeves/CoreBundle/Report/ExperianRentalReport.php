<?php

namespace RentJeeves\CoreBundle\Report;

use DateTime;

class ExperianRentalReport extends RentalReport
{
    protected $records;

    public function getSerializationType()
    {
        return 'csv';
    }

    public function getReportFilename()
    {
        $today = new DateTime();

        return sprintf('renttrack-full-%s.csv', $today->format('Ymd'));
    }

    public function createHeader()
    {

    }

    public function createRecords($reportMonth, $reportYear)
    {
        if (!$this->records) {
            $this->records = array();
            $contractRepo = $this->em->getRepository('RjDataBundle:Contract');
            $contracts = $contractRepo->getContractsForRentalReport($reportMonth, $reportYear);
            $operationRepo = $this->em->getRepository('DataBundle:Operation');

            foreach ($contracts as $contract) {
                $rentOperation = $operationRepo->getRentOperationForMonth($contract->getId(), $reportMonth, $reportYear);
                $this->records[] = new ExperianReportRecord($contract, $rentOperation);
            }
        }
    }
}
