<?php

namespace RentJeeves\CoreBundle\Report\TransUnion;

use RentJeeves\DataBundle\Entity\Contract;
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
        $this->logger->debug('Creating records for TU negative report...');

        $this->records = [];
        $contracts = $this->em->getRepository('RjDataBundle:Contract')
            ->getContractsForTransUnionNegativeReport(
                $params->getMonth(),
                $params->getStartDate(),
                $params->getEndDate()
            );
        $operationRepo = $this->em->getRepository('DataBundle:Operation');

        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            // LastPaidFor always exists b/c contracts were joined with operations
            $lastPaidFor = $operationRepo->getLastContractPaidFor($contract);
            $this->logger->debug(sprintf(
                'Creating TU negative record for contract: #%s. Last paidFor: %s',
                $contract->getId(),
                $lastPaidFor->format('m/d/Y')
            ));
            $reportRecord = new TransUnionReportRecord($contract, $params->getMonth(), $lastPaidFor);
            $this->logger->debug(sprintf(
                'Created TU negative record for contract #%s has status %s',
                $contract->getId(),
                $reportRecord->getLeaseStatus()
            ));
            if ($reportRecord->getLeaseStatus() != TransUnionReportRecord::LEASE_STATUS_CURRENT) {
                $this->records[] = $reportRecord;
            }
        }

        $this->logger->debug(sprintf('TU negative report created! Count of records: %s', count($this->records)));
    }
}
