<?php

namespace RentJeeves\CoreBundle\Report\Experian;

use RentJeeves\CoreBundle\Report\RentalReportData;
use RentJeeves\DataBundle\Entity\Contract;

class ExperianClosureReport extends ExperianRentalReport
{
    const REPORT_TYPE = 'closure';

    /**
     * {@inheritdoc}
     */
    public function createRecords(RentalReportData $params)
    {
        $this->logger->debug('Creating records for Experian closure report...');

        $this->records = [];
        $contracts = $this->em->getRepository('RjDataBundle:Contract')
            ->getContractsForExperianClosureReport($params->getStartDate(), $params->getEndDate());

        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            $this->logger->debug(sprintf('Creating Experian closure record for contract: #%s', $contract->getId()));
            $this->records[] = new ExperianReportRecord($contract);
        }

        $this->logger->debug(sprintf('Experian closure report created! Count of records: %s', count($this->records)));
    }
}
