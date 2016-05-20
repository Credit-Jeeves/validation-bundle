<?php

namespace RentJeeves\ExternalApiBundle\Services\EmailNotifier;

use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;
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
        $beginDate = sprintf('%s 00:00:00', $settings['begin']);
        $endDate = sprintf('%s 23:59:59', $settings['end']);
        /** @var TransactionRepository $repo */
        $repo = $this->em->getRepository('RjDataBundle:Transaction');

        return $repo->getTransactionsForRentTrackReport(
            $settings['groups'],
            $beginDate,
            $endDate,
            ExportReport::EXPORT_BY_PAYMENTS
        );
    }

    /**
     * @param array $settings
     * @throws ExportException
     */
    protected function validateSettings(array $settings)
    {
        if (!isset($settings['groups']) || !isset($settings['begin']) || !isset($settings['end'])) {
            throw new ExportException('Not enough parameters for RentTrackExportReport report');
        }
    }
}
