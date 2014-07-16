<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;
use RentJeeves\LandlordBundle\Accounting\Export\Serializer\ExportSerializerInterface;
use RentJeeves\LandlordBundle\Model\OrderReport;
use DateTime;
use ZipArchive;

/**
 * @Service("accounting.export.promas_archive")
 */
class PromasArchive extends ExportReport
{
    protected $type = 'promas_zip';

    protected $exportReport;
    protected $serializer;

    /**
     * @InjectParams({
     *     "exportReport" = @Inject("accounting.export.promas"),
     *     "serializer" = @Inject("export.serializer.promas"),
     * })
     */
    public function __construct(ExportReport $exportReport, ExportSerializerInterface $serializer)
    {
        $this->exportReport = $exportReport;
        $this->serializer = $serializer;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getContent($settings)
    {
        $this->validateSettings($settings);
        $this->generateFilename($settings);

        $zipArchive = new ZipArchive();
        $tmpZipName = $this->getTempZipFilename();
        if ($zipArchive->open($tmpZipName, ZipArchive::CREATE) !== true) {
            throw new ExportException('Can not create zip archive');
        }

        $orders = $this->getData($settings);
        /** @var Order $order */
        foreach ($orders as $batchId => $batchedOrders) {
            $content = $this->serializer->serialize($batchedOrders);
            $filename = $this->getBatchFilename($batchId, $settings);
            $zipArchive->addFromString($filename, $content);
        }
        $zipArchive->close();
        $result = readfile($tmpZipName);
        unlink($tmpZipName);

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

    protected function getBatchFilename($batchNumber, $params)
    {
        $beginDate = new DateTime($params['begin']);
        $endDate = new DateTime($params['end']);

        return sprintf('Promas_%s_%s_%s.csv', $beginDate->format('Ymd'), $endDate->format('Ymd'), $batchNumber);
    }

    protected function getTempZipFilename()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR. 'report.zip';
    }

    protected function validateSettings($settings)
    {
        if (!isset($settings['begin']) || !isset($settings['end'])) {
            throw new ExportException('Not enough parameters for batch report');
        }
    }
}
