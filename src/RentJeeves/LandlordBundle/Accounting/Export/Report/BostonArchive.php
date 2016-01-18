<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use RentJeeves\LandlordBundle\Accounting\Export\Serializer\ExportSerializerInterface;
use RentJeeves\LandlordBundle\Accounting\Export\ZipArchiveReport;

/**
 * accounting.export.boston_archive
 */
class BostonArchive extends BostonReport
{
    use ZipArchiveReport;

    /**
     * @param ExportReport $exportReport
     * @param ExportSerializerInterface $serializer
     */
    public function __construct(ExportReport $exportReport, ExportSerializerInterface $serializer)
    {
        $this->useReport($exportReport);
        $this->useSerializer($serializer);
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(array $settings)
    {
        $this->validateSettings($settings);
        $this->generateFilename($settings);

        $orders = $this->getData($settings);

        if (empty($orders)) {
            return null;
        }

        $zipArchive = $this->openZipArchive();
        /** @var Order $order */
        foreach ($orders as $batchId => $batchedOrders) {
            $content = $this->serializer->serialize($batchedOrders);
            $filename = $this->getBatchFilename($batchId, $settings);
            $zipArchive->addFromString($filename, $content);
        }
        $result = $this->readZipArchive($zipArchive);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(array $settings)
    {
        $result = [];
        $orders = $this->exportReport->getData($settings);
        /** @var Order $order */
        foreach ($orders as $order) {
            $transactionBatchId = $order->getCompleteTransaction()->getBatchId();
            $result[$transactionBatchId][] = $order;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return 'application/zip';
    }

    /**
     * @param array $params
     */
    protected function generateFilename($params)
    {
        parent::generateFilename($params);
        $fileName = explode('.', $this->filename);
        $this->filename = $fileName[0].'.zip';
    }
}
