<?php

namespace RentJeeves\DataBundle\Validators;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContractDuplicate extends Constraint
{
    public $duplicateMessage = 'error.contract.duplicate';

    public function validatedBy()
    {
        return 'duplicate_contract_validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
