<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\Yardi\FullResident;
use RentJeeves\ImportBundle\PropertyImport\Transformer\YardiTransformer;

class YardiMissoulaRusselSquareTransformer extends YardiTransformer
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
        $address = $accountingSystemRecord->getResidentData()->getUnit()->getUnitAddress()->getUnitAddressLine1();

        return $address;
    }
}
