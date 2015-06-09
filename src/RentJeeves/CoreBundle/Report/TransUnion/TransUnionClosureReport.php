<?php

namespace RentJeeves\CoreBundle\Report\TransUnion;

use RentJeeves\DataBundle\Entity\Contract;
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
        $this->logger->debug('Creating records for TU closure report...');

        $this->records = [];

        $contracts = $this->em->getRepository('RjDataBundle:Contract')
            ->getContractsForTransUnionClosureReport($params->getStartDate(), $params->getEndDate());

        $operationRepo = $this->em->getRepository('DataBundle:Operation');

        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            // LastPaidFor always exists b/c contracts were joined with operations
            $lastPaidFor = $operationRepo->getLastContractPaidFor($contract);
            $this->logger->debug(sprintf(
                'Creating TU closure record for contract: #%s. Last paidFor: %s',
                $contract->getId(),
                $lastPaidFor->format('m/d/Y')
            ));
            // Don't send any payment data, only finished contract data.
            $this->records[] = new TransUnionReportRecord($contract, $params->getMonth(), $lastPaidFor);
        }

        $this->logger->debug(sprintf('TU closure report created! Count of records: %s', count($this->records)));
    }
}
