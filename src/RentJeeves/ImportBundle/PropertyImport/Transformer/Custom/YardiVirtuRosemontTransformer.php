<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\Yardi\FullResident;
use RentJeeves\ImportBundle\PropertyImport\Transformer\YardiTransformer;

class YardiVirtuRosemontTransformer extends YardiTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function getAddress1(FullResident $accountingSystemRecord)
    {
        return $accountingSystemRecord->getResidentData()->getUnit()->getUnitAddress()->getUnitAddressLine1();
    }

    /**
     * {@inheritdoc}
     */
    protected function isAddressHasUnits(FullResident $accountingSystemRecord)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUnitName(FullResident $accountingSystemRecord)
    {
        return null;
    }
}
