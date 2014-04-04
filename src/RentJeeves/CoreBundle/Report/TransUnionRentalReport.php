<?php

namespace RentJeeves\CoreBundle\Report;

use DateTime;

class TransUnionRentalReport extends RentalReport
{
    protected $header;
    protected $records;

    public function getSerializationType()
    {
        return 'tu_rental1';
    }

    public function getReportFilename()
    {
        $today = new DateTime();

        return sprintf('renttrack-%s.txt', $today->format('Ymd'));
    }

    public function createHeader()
    {
        $this->header = new TransUnionReportHeader();
        $lastActivityDate = $this->em->getRepository('RjDataBundle:Contract')->getLastActivityDate();
        $this->header->setActivityDate(new DateTime($lastActivityDate));
    }

    public function createRecords($reportMonth, $reportYear)
    {
        $this->records = array();
        $contracts = $this->em->getRepository('RjDataBundle:Contract')
            ->getContractsForTURentalReport($reportMonth, $reportYear);

        $operationRepo = $this->em->getRepository('DataBundle:Operation');

        foreach ($contracts as $contract) {
            $rentOperation = $operationRepo->getRentOperationForMonth($contract->getId(), $reportMonth, $reportYear);
            $this->records[] = new TransUnionReportRecord($contract, $reportMonth, $reportYear, $rentOperation);
        }
    }
}
