<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\Yardi\FullResident;
use RentJeeves\ImportBundle\PropertyImport\Transformer\YardiTransformer;

class YardiSolanaVistaTransformer extends YardiTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function getAddress1(FullResident $accountingSystemRecord)
    {
        return sprintf('%s Fishermans Dr', $this->getStreetNumber($accountingSystemRecord));
    }

    /**
     * {@inheritdoc}
     */
    protected function getCity(FullResident $accountingSystemRecord)
    {
        return 'Bradenton';
    }

    /**
     * {@inheritdoc}
     */
    protected function getState(FullResident $accountingSystemRecord)
    {
        return 'FL';
    }

    /**
     * {@inheritdoc}
     */
    protected function getZip(FullResident $accountingSystemRecord)
    {
        return '35562';
    }

    /**
     * {@inheritdoc}
     */
    protected function getStreetNumber(FullResident $accountingSystemRecord)
    {
        return parent::getUnitName($accountingSystemRecord);
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
