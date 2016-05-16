<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\Yardi\UnitInformation;
use RentJeeves\ImportBundle\PropertyImport\Transformer\YardiTransformer;

class YardiMissoulaPublicHousingValorTransformer extends YardiTransformer
{
    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return string
     */
    protected function getAddress1(UnitInformation $accountingSystemRecord)
    {
        $address = $accountingSystemRecord->getUnit()->getUnit()->getInformation()->getAddress()->getAddress1();

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
        $unit = preg_replace('/VALOR-/', '', $unit);

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
