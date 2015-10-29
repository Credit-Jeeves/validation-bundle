<?php

namespace RentJeeves\CoreBundle\Report\Experian;

use RentJeeves\CoreBundle\Report\RentalReportData;
use RentJeeves\DataBundle\Entity\Contract;
use CreditJeeves\DataBundle\Entity\Operation;

class ExperianPositiveReport extends ExperianRentalReport
{
    const REPORT_TYPE = 'positive';

    /**
     * {@inheritdoc}
     */
    public function createRecords(RentalReportData $params)
    {
        $this->logger->debug('Creating records for Experian positive report...');

        $this->records = [];
        $contractRepo = $this->em->getRepository('RjDataBundle:Contract');
        $contracts = $contractRepo->getContractsForExperianPositiveReport(
            $params->getMonth(),
            $params->getStartDate(),
            $params->getEndDate()
        );
        $operationRepo = $this->em->getRepository('DataBundle:Operation');

        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            $this->logger->debug(sprintf('Creating Experian positive records for contract: #%s', $contract->getId()));
            $rentOperations = $operationRepo->getExperianRentOperationsForMonth(
                $contract,
                $params->getMonth(),
                $params->getStartDate(),
                $params->getEndDate()
            );
            /** @var Operation $rentOperation */
            foreach ($rentOperations as $rentOperation) {
                $this->logger->debug(sprintf(
                    'Adding Experian positive record for operation #%s, contract #%s',
                    $rentOperation->getId(),
                    $contract->getId()
                ));
                $this->records[] = new ExperianReportRecord($contract, $rentOperation);
            }
        }

        $this->logger->debug(sprintf('Experian positive report created! Count of records: %s', count($this->records)));
    }
}
