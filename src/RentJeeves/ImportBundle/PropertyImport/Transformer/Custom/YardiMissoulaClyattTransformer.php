<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\Yardi\FullResident;
use RentJeeves\ImportBundle\PropertyImport\Transformer\YardiTransformer;

class YardiMissoulaClyattTransformer extends YardiTransformer
{
    /**
     * @param FullResident $accountingSystemRecord
     *
     * @return string
     */
    protected function getAddress1(FullResident $accountingSystemRecord)
    {
        return $accountingSystemRecord->getProperty()->getMarketingName();
    }
}