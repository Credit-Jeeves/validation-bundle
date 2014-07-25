<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;

abstract class ExportReport
{
    protected $filename;

    abstract public function getData($settings);

    abstract public function getContent($settings);

    abstract public function getContentType();

    abstract protected function generateFilename($params);

    public function getFilename()
    {
        if (empty($this->filename)) {
            throw new ExportException('Report filename is not generated yet');
        }

        return $this->filename;
    }
}
