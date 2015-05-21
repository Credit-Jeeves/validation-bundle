<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;

abstract class ExportReport
{
    const EXPORT_BY_PAYMENTS = 'payments';

    const EXPORT_BY_DEPOSITS = 'deposits';

    protected $filename;
    protected $type;
    protected $fileType;

    /**
     * @param array $settings
     * @return ArrayCollection
     */
    abstract public function getData(array $settings);

    /**
     * @param array $settings
     * @return string
     */
    abstract public function getContent(array $settings);

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
