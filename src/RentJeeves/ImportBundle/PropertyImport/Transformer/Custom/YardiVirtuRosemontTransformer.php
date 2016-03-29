<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\Yardi\FullResident;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Customer;
use RentJeeves\ImportBundle\PropertyImport\Transformer\YardiTransformer;

class YardiVirtuRosemontTransformer extends YardiTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function getAddress1(FullResident $accountingSystemRecord)
    {
        $customers = $accountingSystemRecord->getResidentTransactionPropertyCustomer()->getCustomers()->getCustomer();
        /**
         * We have array here and I don't know what exactly user address I should get, so I get first
         *
         * @var Customer $customer
         */
        $customer = reset($customers);

        return $customer->getCustomerAddress()->getCustomerAddress1();
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
