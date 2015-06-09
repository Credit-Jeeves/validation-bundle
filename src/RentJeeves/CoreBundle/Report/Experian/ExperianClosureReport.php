<?php

namespace RentJeeves\CoreBundle\Report\Experian;

use RentJeeves\CoreBundle\Report\RentalReportData;
use RentJeeves\DataBundle\Entity\Contract;

class ExperianClosureReport extends ExperianRentalReport
{
    /**
     * {@inheritdoc}
     */
    public function getReportFilename()
    {
        $today = new \DateTime();

        return sprintf('renttrack-closure-%s.csv', $today->format('Ymd'));
    }

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
