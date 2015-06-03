<?php

namespace RentJeeves\CoreBundle\Report\Experian;

use RentJeeves\CoreBundle\Report\RentalReportData;

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
        $contracts = $this->em->getRepository('RjDataBundle:Contract')
            ->getContractsForExperianClosureReport($params->getStartDate(), $params->getEndDate());

        foreach ($contracts as $contract) {
            $this->records[] = new ExperianReportRecord($contract);
        }
    }
}
