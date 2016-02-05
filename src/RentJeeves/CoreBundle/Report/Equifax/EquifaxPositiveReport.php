<?php

namespace RentJeeves\CoreBundle\Report\Equifax;

use CreditJeeves\DataBundle\Entity\OperationRepository;
use RentJeeves\CoreBundle\Report\RentalReportData;
use RentJeeves\DataBundle\Entity\Contract;

class EquifaxPositiveReport extends EquifaxRentalReport
{
    const REPORT_TYPE = 'positive';

    /**
     * {@inheritdoc}
     */
    protected function createRecords(RentalReportData $params)
    {
        $this->logger->debug('Creating records for Equifax positive report...');

        $this->records = [];
        $operationRepo = $this->getOperationRepository();

        foreach ($this->getContracts($params) as $contractData) {
            $lastPaidFor = $operationRepo->getLastContractPaidFor($contractData['contract']);
            $this->logger->debug(sprintf(
                'Creating Equifax positive record for contract: #%d. Last paidFor: %s',
                $contractData['contract']->getId(),
                $lastPaidFor->format('m/d/Y')
            ));
            $this->records[] = new EquifaxReportRecord(
                $contractData['contract'],
                $params->getMonth(),
                $lastPaidFor,
                $contractData['paid_for'],
                $contractData['total_amount'],
                new \DateTime($contractData['last_payment_date'])
            );
        }

        $this->logger->debug(sprintf('Equifax positive report created! Count of records: %s', count($this->records)));
    }

    /**
     * @param RentalReportData $params
     * @return Contract[]
     */
    protected function getContracts(RentalReportData $params)
    {
        return $this->em->getRepository('RjDataBundle:Contract')
            ->getContractsForEquifaxPositiveReport(
                $params->getMonth(),
                $params->getStartDate(),
                $params->getEndDate()
            );
    }

    /**
     * @return OperationRepository
     */
    protected function getOperationRepository()
    {
        return $this->em->getRepository('DataBundle:Operation');
    }
}
