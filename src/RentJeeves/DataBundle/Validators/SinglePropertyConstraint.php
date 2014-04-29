<?php

namespace RentJeeves\DataBundle\Validators;

use Symfony\Component\Validator\Constraint;

class SinglePropertyConstraint extends Constraint
{
    public $message = 'property.error.can_not_be_single';

    public function validatedBy()
    {
        return 'single_property_validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

}
