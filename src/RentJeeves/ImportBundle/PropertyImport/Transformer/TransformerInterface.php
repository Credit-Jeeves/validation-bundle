<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer;

use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\ImportBundle\Exception\ImportTransformerException;

interface TransformerInterface
{
    /**
     * Transform the accounting system specific data models into generic import records (ImportProperty)
     * and return import id
     *
     * @param array $accountingSystemData accounting system specific model objects
     * @param Import $import new ImportProperties will be add to this Object
     *
     * @throws ImportTransformerException if can`t transform data
     */
    public function transformData(array $accountingSystemData, Import $import);
}
