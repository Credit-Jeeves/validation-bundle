<?php

namespace RentJeeves\ImportBundle\LeaseImport\Transformer;

use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\ImportBundle\Exception\ImportTransformerException;

interface TransformerInterface
{
    /**
     * Transform the accounting system specific data models into generic import records (ImportLease)
     *
     * @param array $accountingSystemData accounting system specific model objects
     * @param Import $import new Import will be add to this Object
     *
     * @throws ImportTransformerException if can`t transform data
     */
    public function transformData(array $accountingSystemData, Import $import);
}
