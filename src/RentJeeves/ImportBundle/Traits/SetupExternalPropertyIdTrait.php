<?php

namespace RentJeeves\ImportBundle\Traits;

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
