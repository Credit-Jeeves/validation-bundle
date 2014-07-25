<?php

namespace RentJeeves\LandlordBundle\Accounting\Export;

use RentJeeves\CoreBundle\DateTime;
use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;
use RentJeeves\LandlordBundle\Accounting\Export\Report\ExportReport;
use RentJeeves\LandlordBundle\Accounting\Export\Serializer\ExportSerializerInterface;
use ZipArchive;

trait ZipArchiveReport
{
    protected $exportReport;
    protected $serializer;

    public function openZipArchive()
    {
        $zipArchive = new ZipArchive();
        if ($zipArchive->open($this->getTempZipFilename(), ZipArchive::CREATE) !== true) {
            throw new ExportException('Can not create zip archive');
        }

        return $zipArchive;
    }

    public function readZipArchive(ZipArchive $zipArchive, $close = true)
    {
        $zipArchive->close();
        $result = readfile($this->getTempZipFilename());
        if ($close) {
            unlink($this->getTempZipFilename());
        }

        return $result;
    }

    public function useReport(ExportReport $exportReport)
    {
        $this->exportReport = $exportReport;
    }

    public function useSerializer(ExportSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    protected function getTempZipFilename()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR. 'report.zip';
    }

    protected function getBatchFilename($batchNumber, $params)
    {
        $beginDate = new DateTime($params['begin']);
        $endDate = new DateTime($params['end']);

        return sprintf(
            '%s_%s_%s_%s.%s',
            $this->exportReport->getType(),
            $beginDate->format('Ymd'),
            $endDate->format('Ymd'),
            $batchNumber,
            $this->exportReport->getFileType()
        );
    }
} 
