<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor\Traits;

trait SetupExternalPropertyIdTrait
{
    /**
     * @var string
     */
    protected $externalPropertyId;

    /**
     * @param string $extPropertyId
     */
    public function setExtPropertyId($extPropertyId)
    {
        $this->externalPropertyId = $extPropertyId;
    }
}
