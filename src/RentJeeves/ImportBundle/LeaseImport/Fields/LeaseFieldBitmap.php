<?php

namespace RentJeeves\ImportBundle\LeaseImport\Fields;

use RentJeeves\CoreBundle\Bitmap\Bitmap;

/**
 * This code should help us to understand what field we want to update in Lease
 * We mark by bitNumber fields in this class
 */
class LeaseFieldBitmap
{
    /**
     * bitmask of fields that can be updated
     *
     * @var int
     */
    protected $updateMask;

    /**
     * bitmap of fields that differ
     *
     * @var Bitmap
     */
    protected $diffBitmap;

    /**
     * @param bool|false $isNew
     */
    public function __construct($isNew = false)
    {
        $this->updateMask = ($isNew) ? LeaseFields::UPDATE_MASK_NEW : LeaseFields::UPDATE_MASK_MATCHED;
        $this->diffBitmap = new Bitmap(0); // no fields different
    }

    /**
     * set as different
     *
     * @param int $bitNumber
     */
    public function markDifferent($bitNumber)
    {
        $this->diffBitmap->setBit($bitNumber);
    }

    /**
     * check if different
     *
     * @param int $bitNumber
     *
     * @return bool
     */
    public function isDifferent($bitNumber)
    {
        return $this->diffBitmap->isBitSet($bitNumber);
    }

    /**
     * check if we need to update this field
     *
     * @param int $bitNumber
     * @return bool
     */
    public function isNeedUpdate($bitNumber)
    {
        return $this->diffBitmap->isBitSetWithinMask($bitNumber, $this->updateMask);
    }

    /**
     * @return int
     */
    public function getUpdateMask()
    {
        return $this->updateMask;
    }

    /**
     * @return int
     */
    public function getDiffBitmap()
    {
        return $this->diffBitmap->getInt();
    }
}
