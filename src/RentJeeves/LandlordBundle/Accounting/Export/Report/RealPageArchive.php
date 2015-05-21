<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;
use RentJeeves\LandlordBundle\Accounting\Export\Serializer\ExportSerializerInterface;
use RentJeeves\LandlordBundle\Accounting\Export\ZipArchiveReport;

/**
 * @Service("accounting.export.real_page_archive")
 */
class RealPageArchive extends ExportReport
{
    use ZipArchiveReport;

    /**
     * @InjectParams({
     *     "exportReport" = @Inject("accounting.export.real_page"),
     *     "serializer" = @Inject("export.serializer.real_page"),
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
        /** @var Order $order */
        foreach ($orders as $order) {
            $transactionBatchId = $order->getCompleteTransaction()->getBatchId();
            $result[$transactionBatchId][] = $order;
        }

        return $result;
    }

    protected function generateFilename($params)
    {
        $this->filename = 'batch_report.zip';
    }

    protected function validateSettings($settings)
    {
        if (!isset($settings['begin']) || !isset($settings['end']) ||
            !isset($settings['export_by']) || !array_key_exists('property', $settings)
        ) {
            throw new ExportException('Not enough parameters for batch report');
        }
    }
}
