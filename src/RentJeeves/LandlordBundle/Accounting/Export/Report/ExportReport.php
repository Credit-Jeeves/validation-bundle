<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;

abstract class ExportReport
{
    const EXPORT_BY_PAYMENTS = 'payments';

    const EXPORT_BY_DEPOSITS = 'deposits';

    protected $filename;
    protected $type;
    protected $fileType;

    abstract public function getData($settings);

    abstract public function getContent($settings);

    abstract public function getContentType();

    public function getType()
    {
        return $this->type;
    }

    public function getFileType()
    {
        return $this->fileType;
    }

    abstract protected function generateFilename($params);

    public function getFilename()
    {
        if (empty($this->filename)) {
            throw new ExportException('Report filename is not generated yet');
        }

        return $this->filename;
    }
}
