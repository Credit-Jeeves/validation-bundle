<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;
use RentJeeves\LandlordBundle\Accounting\Export\Serializer\ExportSerializerInterface;
use DateTime;
use RentJeeves\LandlordBundle\Accounting\Export\ZipArchiveReport;
use ZipArchive;

/**
 * @Service("accounting.export.promas_archive")
 */
class PromasArchive extends ExportReport
{
    use ZipArchiveReport;

    /**
     * @InjectParams({
     *     "exportReport" = @Inject("accounting.export.promas"),
     *     "serializer" = @Inject("export.serializer.promas"),
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

        $zipArchive = $this->openZipArchive();

        $orders = $this->getData($settings);
        /** @var Order $order */
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
            $transactionBatchId = $order->getHeartlandBatchId();
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
        if (!isset($settings['begin']) || !isset($settings['end'])) {
            throw new ExportException('Not enough parameters for batch report');
        }
    }
}
