<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\Yardi\UnitInformation;
use RentJeeves\ImportBundle\PropertyImport\Transformer\YardiTransformer;

class YardiMissoulaPublicHousingMt033007Transformer extends YardiTransformer
{
    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return string
     */
    protected function getAddress1(UnitInformation $accountingSystemRecord)
    {
        $address = $accountingSystemRecord->getProperty()->getAddressLine1();

        return $address;
    }

    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return string
     */
    protected function getUnitName(UnitInformation $accountingSystemRecord)
    {
        $unit = $accountingSystemRecord->getUnit()->getUnitId();
        $unit = preg_replace('/1230/', '', $unit);

        return $unit;
    }

    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return string
     */
    protected function getExternalUnitId(UnitInformation $accountingSystemRecord)
    {
        $unitName = $accountingSystemRecord->getUnit()->getUnitId();
        $externalUnitId = $accountingSystemRecord->getProperty()->getExternalUnitId($unitName);

        return $externalUnitId;
    }
}
