<?php

namespace RentJeeves\CoreBundle\Report\TransUnion;

use RentJeeves\CoreBundle\Report\RentalReportData;

class TransUnionPositiveReport extends TransUnionRentalReport
{
    const REPORT_TYPE = 'positive';

    /**
     * {@inheritdoc}
     */
    protected function createRecords(RentalReportData $params)
    {
        $this->logger->debug('Creating records for TU positive report...');

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
            $this->logger->debug(sprintf(
                'Creating TU negative record for contract: #%s. Last paidFor: %s',
                $contractData['contract']->getId(),
                $lastPaidFor->format('m/d/Y')
            ));
            $this->records[] = new TransUnionReportRecord(
                $contractData['contract'],
                $params->getMonth(),
                $lastPaidFor,
                $contractData['paid_for'],
                $contractData['total_amount'],
                new \DateTime($contractData['last_payment_date'])
            );
        }

        $this->logger->debug(sprintf('TU positive report created! Count of records: %s', count($this->records)));
    }
}
