<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\MRI\Value;
use RentJeeves\ImportBundle\PropertyImport\Transformer\MRITransformer;

class MRIMultiPropertyStreetInBuildingAddressUnitInAddressHubbell extends MRITransformer
{
    /**
     * {@inheritdoc}
     */
    protected function getAllowMultipleProperties(Value $accountingSystemRecord)
    {
        return true;
    }


    /**
     * @param Value $accountingSystemRecord
     *
     * @return string
     */
    protected function getAddress1(Value $accountingSystemRecord)
    {
        $unit_name = $accountingSystemRecord->getAddress();
        $unit_name = (strpos($unit_name, "#") == 0) ? substr($unit_name, 1) : $unit_name;

        // Hubbell has "Non Resident Garage" rentals. If the Building Name is NRG, tack on to unit name.
        $unit_name = ($accountingSystemRecord->getBuildingId() == "NRG") ? "NRG" . "$unit_name" : $unit_name;

        if (strlen($unit_name) && (substr($unit_name,0,1) != "#")) {
            $unit_name = "#" . $unit_name;
        }

        return $accountingSystemRecord->getBuildingAddress() . " " . $unit_name;
    }

    /**
     * @param Value $accountingSystemRecord
     *
     * @return string
     */
    protected function getUnitName(Value $accountingSystemRecord)
    {
        $unit_name = $accountingSystemRecord->getUnitId();

        return ($accountingSystemRecord->getBuildingId() == "NRG") ? "NRG" . "$unit_name" : $unit_name;
    }
}

