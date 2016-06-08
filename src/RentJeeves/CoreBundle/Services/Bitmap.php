<?php

namespace RentJeeves\CoreBundle\Services;

/**
 * service "bitmap"
 */
class Bitmap {

    /**
     * @var int
     */
    protected $bitmap;

    /**
     * @param int $bitmap
     */
    public function __construct($bitmap = 0)
    {
        $this->bitmap = (int) $bitmap;
    }

    /**
     * Marks a specific field as changed.
     *
     * @param int $index the bit you want to set
     */
    public function setBit($index)
    {
        $this->bitmap |= (1 << $index);
    }

    /**
     * Checks to see if a specific bit has been set or not.
     *
     * @param int $index the bit you want to check
     *
     * @return boolean if true, then the field was marked as changed
     */
    public function isBitSet($index)
    {
        return (bool) ($this->bitmap & (1 << $index));
    }

    /**
     * only check bit if it is in mask
     *
     * @param int $index
     * @param int $mask
     * @return bool
     */
    public function isBitSetWithinMask($index, $mask)
    {
        $maskedBitmap = $this->bitmap & $mask;

        return (bool) ($maskedBitmap & (1 << $index));
    }

    /**
     * Unset any bits we don't care about
     * @param int $mask any bits not in the mask will be unset
     * @return self
     */
    public function unsetBitsByMask($mask)
    {
        $this->bitmap = $this->bitmap & ~$mask;

        return $this;
    }

    /**
     * Gets the raw bitmap.
     *
     * Having the full map is useful for iterating through all the fields
     *
     * @return int
     *
     */
    public function getInt()
    {
        return $this->bitmap;
    }
}

