<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use RentJeeves\DataBundle\Enum\ImportType;
use RentJeeves\LandlordBundle\Exception\ImportStorageException;

abstract class StorageAbstract implements StorageInterface
{
    const IMPORT_MAPPING = 'importMapping';

    const IMPORT_STORAGE_TYPE = 'importStorageType';

    const IMPORT_OFFSET_START = 'importOffsetStart';

    const IMPORT_ONLY_EXCEPTION = 'importOnlyException';

    const IMPORT_TYPE = 'importType';

    const IMPORT_SUMMARY_REPORT_PUBLIC_ID = 'importSummaryReportPublicId';

    protected $session;

    protected $mapping;

    protected $availableTypes = ['csv', 'yardi', 'resman', 'mri', 'amsi'];

    public function setMapping(array $mappedData)
    {
        $this->session->set(self::IMPORT_MAPPING, json_encode($mappedData));
    }

    public function getMapping()
    {
        if (empty($this->mapping)) {
            $this->mapping = json_decode($this->session->get(self::IMPORT_MAPPING), true);
        }

        return $this->mapping;
    }

    public function setStorageType($type)
    {
        if (!in_array($type, $this->availableTypes)) {
            throw new ImportStorageException(sprintf('Not available type "%s"', $type));
        }

        $this->session->set(self::IMPORT_STORAGE_TYPE, $type);
    }

    public function getStorageType()
    {
        return $this->session->get(self::IMPORT_STORAGE_TYPE);
    }

    public function setImportType($type)
    {
        if (!ImportType::isValid($type)) {
            throw new ImportStorageException(sprintf('Not available import type "%s"', $type));
        }

        $this->session->set(self::IMPORT_TYPE, $type);
    }

    public function getImportType()
    {
        return $this->session->get(self::IMPORT_TYPE);
    }

    public function setImportSummaryReportPublicId($id)
    {
        $this->session->set(self::IMPORT_SUMMARY_REPORT_PUBLIC_ID, $id);
    }

    public function getImportSummaryReportPublicId()
    {
        return $this->session->get(self::IMPORT_SUMMARY_REPORT_PUBLIC_ID, null);
    }

    public function getOffsetStart()
    {
        return $this->session->get(self::IMPORT_OFFSET_START, 0);
    }

    public function setOffsetStart($start)
    {
        return $this->session->set(self::IMPORT_OFFSET_START, $start);
    }

    public function setOnlyException($exceptionOnly)
    {
        $this->session->set(self::IMPORT_ONLY_EXCEPTION, $exceptionOnly);
    }

    public function isOnlyException()
    {
        return $this->session->get(self::IMPORT_ONLY_EXCEPTION);
    }
}
