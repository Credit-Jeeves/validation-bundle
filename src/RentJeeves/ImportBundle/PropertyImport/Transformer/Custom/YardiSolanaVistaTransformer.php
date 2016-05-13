<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\Yardi\UnitInformation;
use RentJeeves\ImportBundle\PropertyImport\Transformer\YardiTransformer;

class YardiSolanaVistaTransformer extends YardiTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function getAddress1(UnitInformation $accountingSystemRecord)
    {
        return sprintf('%s Fishermans Dr', $this->getStreetNumber($accountingSystemRecord));
    }

    /**
     * {@inheritdoc}
     */
    protected function getCity(UnitInformation $accountingSystemRecord)
    {
        return 'Bradenton';
    }

    /**
     * {@inheritdoc}
     */
    protected function getState(UnitInformation $accountingSystemRecord)
    {
        return 'FL';
    }

    /**
     * {@inheritdoc}
     */
    protected function getZip(UnitInformation $accountingSystemRecord)
    {
        return '35562';
    }

    /**
     * {@inheritdoc}
     */
    protected function getStreetNumber(UnitInformation $accountingSystemRecord)
    {
        return parent::getUnitName($accountingSystemRecord);
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
