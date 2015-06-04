<?php

namespace RentJeeves\CoreBundle\Report\TransUnion;

use RentJeeves\CoreBundle\Report\RentalReportData;

class TransUnionPositiveReport extends TransUnionRentalReport
{
    /**
     * {@inheritdoc}
     */
    public function getReportFilename()
    {
        $today = new \DateTime();

        return sprintf('renttrack-positive-%s.txt', $today->format('Ymd'));
    }

    /**
     * {@inheritdoc}
     */
    protected function createRecords(RentalReportData $params)
    {
        $this->records = [];
        $contracts = $this->em->getRepository('RjDataBundle:Contract')
            ->getContractsForTransUnionPositiveReport(
                $params->getMonth(),
                $params->getStartDate(),
                $params->getEndDate()
            );
        $operationRepo = $this->em->getRepository('DataBundle:Operation');

        foreach ($contracts as $contractData) {
            $lastPaidFor = $operationRepo->getLastContractPaidFor($contractData['contract']);
            $this->records[] = new TransUnionReportRecord(
                $contractData['contract'],
                $params->getMonth(),
                $lastPaidFor,
                $contractData['paid_for'],
                $contractData['total_amount'],
                new \DateTime($contractData['last_payment_date'])
            );
        }
    }
}
