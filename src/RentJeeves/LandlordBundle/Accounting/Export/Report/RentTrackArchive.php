<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;
use RentJeeves\LandlordBundle\Accounting\Export\Serializer\ExportSerializerInterface;
use RentJeeves\LandlordBundle\Accounting\Export\ZipArchiveReport;

/**
 * @Service("accounting.export.renttrack_archive")
 */
class RentTrackArchive extends ExportReport
{
    use ZipArchiveReport;

    /**
     * @InjectParams({
     *     "exportReport" = @Inject("accounting.export.renttrack"),
     *     "serializer" = @Inject("export.serializer.renttrack"),
     * })
     */
    public function __construct(ExportReport $exportReport, ExportSerializerInterface $serializer)
    {
        $this->useReport($exportReport);
        $this->useSerializer($serializer);
    }

    public function getContent($settings)
    {
        $this->validateSettings($settings);
        $this->generateFilename($settings);

        $orders = $this->getData($settings);

        if (empty($orders)) {
            return null;
        }

        $zipArchive = $this->openZipArchive();

        foreach ($orders as $batchId => $batchedOrders) {
            $content = $this->serializer->serialize($batchedOrders);
            $filename = $this->getBatchFilename($batchId, $settings);
            $zipArchive->addFromString($filename, $content);
        }
        $result = $this->readZipArchive($zipArchive);

        return $result;
    }

    public function getContentType()
    {
        return 'application/zip';
    }

    public function getData($settings)
    {
        $result = array();
        $orders = $this->exportReport->getData($settings);
        /** @var Transaction $transaction */
        foreach ($orders as $transaction) {
            $transactionBatchId = $transaction->getOrder()->getCompleteTransaction()->getHeartlandBatchId();
            $result[$transactionBatchId][] = $transaction;
        }

        return $result;
    }

    protected function generateFilename($params)
    {
        $this->filename = 'batch_report.zip';
    }

    protected function validateSettings($settings)
    {
        if (!isset($settings['begin']) || !isset($settings['end']) || !isset($settings['export_by'])) {
            throw new ExportException('Not enough parameters for batch report');
        }
    }
}
