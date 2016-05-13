<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\Yardi\UnitInformation;
use RentJeeves\ImportBundle\PropertyImport\Transformer\YardiTransformer;

class YardiMissoulaPublicHousingBusinessTransformer extends YardiTransformer
{
    const ADDRESS_PART = 0;
    const UNIT_PART = 1;

    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return string
     */
    protected function getAddress1(UnitInformation $accountingSystemRecord)
    {
        $addressParts = $this->getAddressParts($accountingSystemRecord);
        $address = $addressParts[self::ADDRESS_PART];

        return $address;
    }

    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return string
     */
    protected function getUnitName(UnitInformation $accountingSystemRecord)
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
     * @param UnitInformation $accountingSystemRecord
     *
     * @return bool
     */
    protected function isAddressHasUnits(UnitInformation $accountingSystemRecord)
    {
        $addressParts = $this->getAddressParts($accountingSystemRecord);
        $result = count($addressParts) > 1;

        return $result;
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

    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return string
     */
    protected function getCity(UnitInformation $accountingSystemRecord)
    {
        return 'Missoula';
    }

    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return string
     */
    protected function getState(UnitInformation $accountingSystemRecord)
    {
        return 'MT';
    }

    protected function getAddressParts(UnitInformation $accountingSystemRecord)
    {
        $address = $accountingSystemRecord->getUnit()->getUnit()->getInformation()->getAddress()->getAddress1();
        $parts = explode('#', $address);

        return $parts;
    }
}
