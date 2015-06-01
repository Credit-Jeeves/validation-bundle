<?php

namespace RentJeeves\CoreBundle\Report\TransUnion;

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
        $this->records = [];
        $contracts = $this->em->getRepository('RjDataBundle:Contract')
            ->getFinishedTransUnionContracts($params->getStartDate(), $params->getEndDate());

        $operationRepo = $this->em->getRepository('DataBundle:Operation');

        foreach ($contracts as $contract) {
            $reportData = $operationRepo->getTransUnionRentOperationsForMonth($contract->getId(), $params->getMonth());
            $paidFor = isset($reportData[0]['paid_for'])? new \DateTime($reportData[0]['paid_for']) : null;
            $totalAmount = isset($reportData[0]['total_amount'])? $reportData[0]['total_amount'] : null;
            $paymentDate = isset($reportData[0]['last_payment_date'])?
                new \DateTime($reportData[0]['last_payment_date']) : null;
            $this->records[] = new TransUnionReportRecord(
                $contract,
                $params->getMonth(),
                $paidFor,
                $totalAmount,
                $paymentDate
            );
        }
    }
}
