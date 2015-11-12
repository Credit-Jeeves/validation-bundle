<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

interface ExternalApiStorageInterface extends StorageInterface
{
    /**
     * @param array|object $residentData
     * @return bool
     */
    public function saveToFile($residentData);
}
