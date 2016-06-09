<?php

namespace RentJeeves\ImportBundle\LeaseImport\Fields;

use RentJeeves\CoreBundle\Bitmap\Bitmap;

/**
 * Here we set statuses about lease
 */
class ImportLeaseStatusBitmap
{
    /**
     * @var Bitmap
     */
    protected $statusBitmap;

    public function __construct()
    {
        $this->statusBitmap = new Bitmap();
    }

    /**
     * @param int $bitNumber
     */
    public function setStatus($bitNumber)
    {
        $this->statusBitmap->setBit($bitNumber);
    }

    /**
     * @param int $bitNumber
     *
     * @return bool
     */
    public function isStatusSet($bitNumber)
    {
        return $this->statusBitmap->isBitSet($bitNumber);
    }

    /**
     * @return int
     */
    public function getStatusBitmap()
    {
        return $this->statusBitmap->getInt();
    }
}
