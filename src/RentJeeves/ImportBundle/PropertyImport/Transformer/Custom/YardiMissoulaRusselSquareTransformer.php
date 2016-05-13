<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\Yardi\UnitInformation;
use RentJeeves\ImportBundle\PropertyImport\Transformer\YardiTransformer;

class YardiMissoulaRusselSquareTransformer extends YardiTransformer
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
        $address = $accountingSystemRecord->getUnit()->getUnit()->getInformation()->getAddress()->getAddress1();

        return $address;
    }
}
