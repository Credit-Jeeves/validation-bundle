<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use CreditJeeves\DataBundle\Entity\Operation;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;
use RentJeeves\LandlordBundle\Accounting\Export\Serializer\ExportSerializerInterface;
use RentJeeves\LandlordBundle\Accounting\Export\ZipArchiveReport;

/**
 * @Service("accounting.export.yardi_archive")
 */
class YardiArchive extends ExportReport
{
    use ZipArchiveReport;
    /**
     * @InjectParams({
     *     "exportReport" = @Inject("accounting.export.yardi"),
     *     "serializer" = @Inject("export.serializer.yardi"),
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

        $data = $this->getData($settings);

        if (empty($data)) {
            return null;
        }

        $zipArchive = $this->openZipArchive();

        foreach ($data as $batchId => $batchedData) {
            $content = $this->serializer->serialize($batchedData);
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
        $operations = $this->exportReport->getData($settings);
        /** @var Operation $operation */
        foreach ($operations as $operation) {
            $transactionBatchId = $operation->getOrder()->getCompleteTransaction()->getBatchId();
            $result[$transactionBatchId][] = $operation;
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
