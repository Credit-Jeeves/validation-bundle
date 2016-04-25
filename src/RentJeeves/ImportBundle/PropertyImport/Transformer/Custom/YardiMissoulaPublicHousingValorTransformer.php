<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\Yardi\FullResident;
use RentJeeves\ImportBundle\PropertyImport\Transformer\YardiTransformer;

class YardiMissoulaPublicHousingValorTransformer extends YardiTransformer
{
    /**
     * @param FullResident $accountingSystemRecord
     *
     * @return string
     */
    protected function getAddress1(FullResident $accountingSystemRecord)
    {
        $address = $accountingSystemRecord->getResidentData()->getUnit()->getUnitAddress()->getUnitAddressLine1();

        return $address;
    }

    /**
     * @param FullResident $accountingSystemRecord
     *
     * @return string
     */
    protected function getUnitName(FullResident $accountingSystemRecord)
    {
        $unit = $accountingSystemRecord->getResidentTransactionPropertyCustomer()->getUnit()->getUnitId();
        $unit = preg_replace('/VALOR-/', '', $unit);

        return $unit;
    }

    /**
     * @param FullResident $accountingSystemRecord
     *
     * @return string
     */
    protected function getExternalUnitId(FullResident $accountingSystemRecord)
    {
        $unitName = $accountingSystemRecord->getResidentTransactionPropertyCustomer()->getUnit()->getUnitId();
        $externalUnitId = $accountingSystemRecord->getProperty()->getExternalUnitId($unitName);

        return $externalUnitId;
    }
}
