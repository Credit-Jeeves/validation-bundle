<?php

namespace RentJeeves\CoreBundle\Report\TransUnion;

use RentJeeves\CoreBundle\Report\RentalReportData;

class TransUnionClosureReport extends TransUnionRentalReport
{
    /**
     * {@inheritdoc}
     */
    public function getReportFilename()
    {
        $today = new \DateTime();

        return sprintf('renttrack-closure-%s.txt', $today->format('Ymd'));
    }

    /**
     * {@inheritdoc}
     */
    protected function createRecords(RentalReportData $params)
    {
        $this->records = [];
        $contracts = $this->em->getRepository('RjDataBundle:Contract')
            ->getContractsForTransUnionClosureReport($params->getStartDate(), $params->getEndDate());

        $operationRepo = $this->em->getRepository('DataBundle:Operation');

        foreach ($contracts as $contract) {
            $lastPaidFor = $operationRepo->getLastContractPaidFor($contract);
            // Don't send any payment data, only finished contract data.
            $this->records[] = new TransUnionReportRecord($contract, $params->getMonth(), $lastPaidFor);
        }
    }
}
