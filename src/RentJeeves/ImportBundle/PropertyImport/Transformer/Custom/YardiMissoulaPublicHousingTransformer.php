<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\Yardi\FullResident;
use RentJeeves\ImportBundle\PropertyImport\Transformer\YardiTransformer;

class YardiMissoulaPublicHousingTransformer extends YardiTransformer
{
    const ADDRESS_PART = 0;
    const UNIT_PART = 1;

    /**
     * @param FullResident $accountingSystemRecord
     *
     * @return string
     */
    protected function getAddress1(FullResident $accountingSystemRecord)
    {
        $addressParts = $this->getAddressParts($accountingSystemRecord);
        $address = $addressParts[self::ADDRESS_PART];

        return $address;
    }

    /**
     * @param FullResident $accountingSystemRecord
     *
     * @return string
     */
    protected function getUnitName(FullResident $accountingSystemRecord)
    {
        $addressParts = $this->getAddressParts($accountingSystemRecord);

        if (isset($addressParts[self::UNIT_PART])) {
            $unit = $addressParts[self::UNIT_PART];
        } else {
            $unit = null;
        }

        return $unit;
    }

    /**
     * @param FullResident $accountingSystemRecord
     *
     * @return bool
     */
    protected function isAddressHasUnits(FullResident $fullResident)
    {
        $addressParts = $this->getAddressParts($fullResident);
        $result = count($addressParts) > 1;

        return $result;
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

    protected function getAddressParts(FullResident $accountingSystemRecord)
    {
        $address = $accountingSystemRecord->getResidentData()->getUnit()->getUnitAddress()->getUnitAddressLine1();
        $parts = explode('#', $address);

        return $parts;
    }
}
