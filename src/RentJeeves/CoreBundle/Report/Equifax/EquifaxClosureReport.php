<?php

namespace RentJeeves\CoreBundle\Report\Equifax;

use CreditJeeves\DataBundle\Entity\OperationRepository;
use RentJeeves\CoreBundle\Report\RentalReportData;
use RentJeeves\DataBundle\Entity\Contract;

class EquifaxClosureReport extends EquifaxRentalReport
{
    const REPORT_TYPE = 'closure';

    /**
     * {@inheritdoc}
     */
    protected function createRecords(RentalReportData $params)
    {
        $this->logger->debug('Creating records for Equifax closure report...');

        $this->records = [];
        $operationRepo = $this->getOperationRepository();

        foreach ($this->getContracts($params) as $contract) {
            // LastPaidFor always exists b/c contracts were joined with operations
            $lastPaidFor = $operationRepo->getLastContractPaidFor($contract);
            $this->logger->debug(sprintf(
                'Creating Equifax closure record for contract: #%d. Last paidFor: %s',
                $contract->getId(),
                $lastPaidFor->format('m/d/Y')
            ));
            // Don't send any payment data, only finished contract data.
            $this->records[] = new EquifaxReportRecord($contract, $params->getMonth(), $lastPaidFor);
        }

        $this->logger->debug(sprintf('Equifax closure report created! Count of records: %s', count($this->records)));
    }

    /**
     * @param RentalReportData $params
     * @return Contract[]
     */
    protected function getContracts(RentalReportData $params)
    {
        return $this->em->getRepository('RjDataBundle:Contract')
            ->getContractsForEquifaxClosureReport($params->getStartDate(), $params->getEndDate());
    }

    /**
     * @return OperationRepository
     */
    protected function getOperationRepository()
    {
        return $this->em->getRepository('DataBundle:Operation');
    }
}
