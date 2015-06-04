<?php

namespace RentJeeves\CoreBundle\Report\TransUnion;

use RentJeeves\CoreBundle\Report\RentalReportData;

class TransUnionNegativeReport extends TransUnionRentalReport
{
    /**
     * {@inheritdoc}
     */
    public function getReportFilename()
    {
        $today = new \DateTime();

        return sprintf('renttrack-negative-%s.txt', $today->format('Ymd'));
    }

    /**
     * {@inheritdoc}
     */
    protected function createRecords(RentalReportData $params)
    {
        $this->records = [];
        $contracts = $this->em->getRepository('RjDataBundle:Contract')
            ->getContractsForTransUnionNegativeReport(
                $params->getMonth(),
                $params->getStartDate(),
                $params->getEndDate()
            );
        $operationRepo = $this->em->getRepository('DataBundle:Operation');

        foreach ($contracts as $contract) {
            $lastPaidFor = $operationRepo->getLastContractPaidFor($contract);
            $reportRecord = new TransUnionReportRecord($contract, $params->getMonth(), $lastPaidFor);
            if ($reportRecord->getLeaseStatus() != TransUnionReportRecord::LEASE_STATUS_CURRENT) {
                $this->records[] = $reportRecord;
            }
        }
    }
}
