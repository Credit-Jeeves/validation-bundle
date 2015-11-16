<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

interface ExternalApiStorageInterface extends StorageInterface
{
    /**
     * @param array|object $residentData
     * @param string $externalPropertyId
     * @return bool
     */
    public function saveToFile($residentData, $externalPropertyId = null);
}
