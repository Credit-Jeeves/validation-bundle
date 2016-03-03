<?php

namespace RentJeeves\ExternalApiBundle\Services;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\LandlordBundle\Accounting\Export\Report\ExportReport;
use RentJeeves\LandlordBundle\Accounting\Export\Report\RentTrackReport;

class RentTrackExportReport extends RentTrackReport
{
    /**
     * {@inheritdoc}
     */
    public function getData(array $settings)
    {
        $this->softDeleteableControl->disable();
        /** @var Holding $holding */
        $holding = $settings['holding'];
        $beginDate = sprintf('%s 00:00:00', $settings['begin']);
        $endDate = sprintf('%s 23:59:59', $settings['end']);
        $groups = $holding->getGroups();
        /** @var TransactionRepository $repo */
        $repo = $this->em->getRepository('RjDataBundle:Transaction');

        return $repo->getTransactionsForRentTrackReport(
            $groups,
            $beginDate,
            $endDate,
            ExportReport::EXPORT_BY_PAYMENTS
        );
    }
}

