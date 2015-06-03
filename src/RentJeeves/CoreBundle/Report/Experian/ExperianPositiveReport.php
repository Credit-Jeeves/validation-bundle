<?php

namespace RentJeeves\CoreBundle\Report\Experian;

use RentJeeves\CoreBundle\Report\RentalReportData;

class ExperianPositiveReport extends ExperianRentalReport
{
    /**
     * {@inheritdoc}
     */
    public function getReportFilename()
    {
        $today = new \DateTime();

        return sprintf('renttrack-positive-%s.csv', $today->format('Ymd'));
    }

    /**
     * {@inheritdoc}
     */
    public function createRecords(RentalReportData $params)
    {
        $this->records = [];
        $contractRepo = $this->em->getRepository('RjDataBundle:Contract');
        $contracts = $contractRepo->getContractsForExperianPositiveReport(
            $params->getMonth(),
            $params->getStartDate(),
            $params->getEndDate()
        );
        $operationRepo = $this->em->getRepository('DataBundle:Operation');

        foreach ($contracts as $contract) {
            $rentOperations = $operationRepo->getExperianRentOperationsForMonth(
                $contract,
                $params->getMonth(),
                $params->getStartDate(),
                $params->getEndDate()
            );
            foreach ($rentOperations as $rentOperation) {
                $this->records[] = new ExperianReportRecord($contract, $rentOperation);
            }
        }
    }
}
