<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\Yardi\UnitInformation;
use RentJeeves\ImportBundle\PropertyImport\Transformer\YardiTransformer;

class YardiMissoulaClyattTransformer extends YardiTransformer
{
    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return string
     */
    protected function getAddress1(UnitInformation $accountingSystemRecord)
    {
        return $accountingSystemRecord->getProperty()->getMarketingName();
    }
}
