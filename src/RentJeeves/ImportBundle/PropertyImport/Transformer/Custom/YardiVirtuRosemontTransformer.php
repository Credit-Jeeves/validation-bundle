<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\Yardi\UnitInformation;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Customer;
use RentJeeves\ImportBundle\PropertyImport\Transformer\YardiTransformer;

class YardiVirtuRosemontTransformer extends YardiTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function getAddress1(UnitInformation $accountingSystemRecord)
    {
        return $accountingSystemRecord->getUnit()->getUnit()->getInformation()->getAddress()->getAddress1();
    }

    /**
     * {@inheritdoc}
     */
    protected function isAddressHasUnits(UnitInformation $accountingSystemRecord)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUnitName(UnitInformation $accountingSystemRecord)
    {
        return null;
    }
}
