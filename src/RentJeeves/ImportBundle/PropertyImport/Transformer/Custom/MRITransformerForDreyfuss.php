<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\MRI\Value;
use RentJeeves\ImportBundle\PropertyImport\Transformer\MRITransformer;

class MRITransformerForDreyfuss extends MRITransformer
{
    /**
     * {@inheritdoc}
     */
    protected function getAllowMultipleProperties(Value $accountingSystemRecord)
    {
        return true;
    }
}
