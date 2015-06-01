<?php

namespace RentJeeves\CoreBundle\Report\Experian;

use RentJeeves\CoreBundle\Report\RentalReport;

class ExperianRentalReport implements RentalReport
{
    protected $records;

    public function getSerializationType()
    {
        return 'csv';
    }

    public function isEmpty()
    {
        return count($this->records) == 0;
    }

    public function getReportFilename()
    {
        $today = new \DateTime();

        return sprintf('renttrack-raw-%s.csv', $today->format('Ymd'));
    }

    public function createHeader($params)
    {

    }

    public function createRecords($month, $year)
    {
        if (!$this->records) {
            $this->records = array();
            $contractRepo = $this->em->getRepository('RjDataBundle:Contract');
            $contracts = $contractRepo->getContractsForExperianRentalReport($month, $year);
            $operationRepo = $this->em->getRepository('DataBundle:Operation');

            foreach ($contracts as $contract) {
                $rentOperations = $operationRepo->getExperianRentOperationsForMonth($contract->getId(), $month, $year);
                foreach ($rentOperations as $rentOperation) {
                    $this->records[] = new ExperianReportRecord($contract, $rentOperation);
                }
            }
        }
    }
}
